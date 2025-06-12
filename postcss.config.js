module.exports = {
  plugins: [
    require('cssnano')({
      preset: ['default', {
      discardComments: { removeAll: true },
      normalizeWhitespace: true,
      cssDeclarationSorter: true,
      mergeRules: false,
      normalizePositions: false,
      reduceTransforms: false
      }]
    })
  ]
}