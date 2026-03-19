const path = require('path')

module.exports = {
  plugins: {
    tailwindcss: {
      // Абсолютный путь: надёжно при любом cwd (Docker / локально из frontend/).
      config: path.resolve(__dirname, 'tailwind.config.js'),
    },
    autoprefixer: {},
  },
}
