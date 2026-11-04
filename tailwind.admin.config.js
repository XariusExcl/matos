const base = require('./tailwind.config.js');

module.exports = {
  ...base,
  important: true,
  corePlugins: { preflight: false },
};
