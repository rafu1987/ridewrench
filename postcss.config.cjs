module.exports = {
  plugins: {
    autoprefixer: {},
    'postcss-pxtorem': {
      rootValue: 16,
      unitPrecision: 5,
      propList: [
        'font',
        'font-size',
        'line-height',
        'letter-spacing',
        'word-spacing',
        'margin',
        'margin-*',
        'padding',
        'padding-*',
        'gap',
        'row-gap',
        'column-gap',
        'top',
        'right',
        'bottom',
        'left',
        'width',
        'height',
        'min-width',
        'min-height',
        'max-width',
        'max-height',
        'border-radius'
      ],
      selectorBlackList: ['.no-rem'],
      replace: true,
      mediaQuery: false,
      minPixelValue: 2,
      exclude: /node_modules/i
    }
  }
}
