const path = require( 'path' );

module.exports = {
	mode: 'production',
	entry: './src/ua-theme-app/index.js',
	output: {
		path: path.resolve( __dirname ) + '/../../assets/scripts/',
		filename: 'ua-theme-app.js',
	},
	module: {
		rules: [
			{
				test: /\.m?js$/,
				exclude: /node_modules/,
				use: {
					loader: 'babel-loader',
					options: {
						presets: [
							[ '@babel/preset-env', { targets: 'defaults' } ],
							[ '@babel/preset-react', { targets: 'defaults' } ],
						],
					},
				},
			},
		],
	},

};
