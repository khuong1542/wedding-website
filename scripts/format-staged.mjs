import { spawnSync } from 'node:child_process';
import { readFile, writeFile, lstat } from 'node:fs/promises';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const root = fileURLToPath(new URL('../', import.meta.url));
process.chdir(root);

function run(command, args, options = {}) {
  const result = spawnSync(command, args, { stdio: 'inherit', ...options });
  if (result.error) throw result.error;
  if (result.status !== 0)
    throw new Error(`${command} thất bại (${result.status}).`);
}

async function tool(name, args) {
  const directory = path.join(root, 'apps/node_modules', name);
  const pkg = JSON.parse(
    await readFile(path.join(directory, 'package.json'), 'utf8'),
  );
  const bin = typeof pkg.bin === 'string' ? pkg.bin : Object.values(pkg.bin)[0];
  run(process.execPath, [path.join(directory, bin), ...args]);
}

try {
  const files = [];
  for (const argument of process.argv.slice(2)) {
    const file = path.resolve(argument);
    const relative = path.relative(root, file).split(path.sep).join('/');
    if (relative.startsWith('../') || path.isAbsolute(relative)) {
      throw new Error(`File nằm ngoài dự án: ${argument}`);
    }
    if (
      /(^|\/)(node_modules|vendor|\.git)(\/|$)/.test(relative) ||
      /^apps\/(storage|bootstrap\/cache|public\/build)\//.test(relative)
    )
      continue;
    if (!(await lstat(file)).isFile()) continue;
    files.push(file);
  }

  const php = files.filter(
    (file) => file.endsWith('.php') && !file.endsWith('.blade.php'),
  );
  if (php.length) {
    const local = spawnSync(
      'php',
      [
        '-r',
        'exit(extension_loaded("mbstring") && extension_loaded("dom") && extension_loaded("xml") ? 0 : 1);',
      ],
      { stdio: 'ignore' },
    );
    if (local.status === 0) {
      run('php', ['apps/vendor/bin/pint', '--config=apps/pint.json', ...php]);
    } else {
      const containerFiles = php.map((file) => {
        const relative = path.relative(path.join(root, 'apps'), file);
        if (relative.startsWith('..'))
          throw new Error('PHP ngoài apps/ cần PHP local có đủ extension.');
        return `/var/www/html/${relative.split(path.sep).join('/')}`;
      });
      const user = process.getuid
        ? ['--user', `${process.getuid()}:${process.getgid()}`]
        : [];
      run('docker', [
        'compose',
        'run',
        '--rm',
        '--no-deps',
        '-T',
        ...user,
        'app',
        'php',
        'vendor/bin/pint',
        '--config=pint.json',
        ...containerFiles,
      ]);
    }
  }

  for (const file of files) {
    if (file.endsWith('.blade.php')) {
      await tool('blade-formatter', ['--write', file]);
    } else if (/\.(js|mjs|cjs|css|yml|yaml)$/.test(file)) {
      await tool('prettier', ['--write', '--config', '.prettierrc.json', file]);
      if (file.endsWith('.css')) {
        await tool('stylelint', [
          '--fix',
          '--config',
          'apps/stylelint.config.mjs',
          file,
        ]);
      }
    }

    // Chỉ chuẩn hóa văn bản UTF-8; không sửa file nhị phân hoặc symlink.
    const buffer = await readFile(file);
    if (buffer.includes(0)) continue;
    let content;
    try {
      content = new TextDecoder('utf-8', { fatal: true }).decode(buffer);
    } catch {
      continue;
    }
    const formatted = content.replace(/\r\n?/g, '\n').replace(/\n*$/, '\n');
    if (formatted !== content) await writeFile(file, formatted);
  }
} catch (error) {
  console.error(error.message);
  process.exitCode = 1;
}
