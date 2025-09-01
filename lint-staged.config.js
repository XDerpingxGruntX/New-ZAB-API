export default {
    '**/*.php*': ['vendor/bin/duster fix'],
    '**/*.{ts,vue}': ['npx eslint --fix', 'npx prettier --write  --ignore-unknown'],
};
