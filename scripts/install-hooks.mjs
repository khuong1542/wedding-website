import { spawnSync } from 'node:child_process';
import { fileURLToPath } from 'node:url';

const root = fileURLToPath(new URL('../', import.meta.url));
const git = spawnSync('git', ['rev-parse', '--show-toplevel'], {
  cwd: root,
  encoding: 'utf8',
});

if (git.status !== 0) {
  console.log(
    'Chưa có Git repository hợp lệ. Sau khi khởi tạo/clone Git, chạy: npm --prefix apps run hooks:install',
  );
} else {
  const result = spawnSync(
    process.execPath,
    [
      fileURLToPath(
        new URL('../apps/node_modules/lefthook/bin/index.js', import.meta.url),
      ),
      'install',
    ],
    { cwd: root, stdio: 'inherit' },
  );
  if (result.error) throw result.error;
  process.exitCode = result.status ?? 1;
}
