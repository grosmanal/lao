module.exports = {
  verbose: true,
  setupFilesAfterEnv: ['<rootDir>/jest.setup.js'],
  roots: ["<rootDir>/assets/components/"],
  moduleFileExtensions: ['js', 'vue'],
  moduleNameMapper: {
    '^@/(.*)$': '<rootDir>/assets/components/$1',
  },
  transform: {
    "^.+\\.js$": "babel-jest",
    "^.+\\.vue$": "vue-jest"
  },
  snapshotSerializers: [
    "<rootDir>/node_modules/jest-serializer-vue"
  ]
}
