export default {
  plugins: ['@stylistic/stylelint-plugin'],
  rules: {
    'rule-empty-line-before': ['always', { except: ['first-nested'] }],
    '@stylistic/indentation': 4,
    '@stylistic/selector-list-comma-newline-after': 'always',
  },
};
