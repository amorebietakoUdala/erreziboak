const Encore = require('@symfony/webpack-encore');

// Manually configure the runtime environment if not already configured yet by the "encore" command.
// It's useful when you use tools that rely on webpack.config.js file.
if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
// directory where compiled assets will be stored
    .setOutputPath('public/build/')
    // public path used by the web server to access the output path
    .setPublicPath('/erreziboak/build/')
    // only needed for CDN's or sub-directory deploy
    .setManifestKeyPrefix('build/')

/*
 * ENTRY CONFIG
 *
 * Add 1 entry for each "page" of your app
 * (including one that's included on every page - e.g. "app")
 *
 * Each entry will result in one JavaScript file (e.g. app.js)
 * and one CSS file (e.g. app.css) if your JavaScript imports CSS.
 */
.addEntry('app', './assets/js/app.js')
    .addEntry('login', './assets/js/security/login.js')
    .addEntry('user_list', './assets/js/user/list.js')
    .addEntry('user_edit', './assets/js/user/edit.js')
    .addEntry('user_new', './assets/js/user/new.js')
    .addEntry('receiptsFile_upload', './assets/js/receipts_file/upload.js')
    .addEntry('receiptsFile_list', './assets/js/receipts_file/list.js')
    .addEntry('returnsFiles_upload', './assets/js/returns_files/upload.js')
    .addEntry('returnsFiles_list', './assets/js/returns_files/list.js')
    .addEntry('receipt_search', './assets/js/receipt/search.js')
    .addEntry('payment_list', './assets/js/payment/list.js')
    .addEntry('payment_show', './assets/js/payment/show.js')
    .addEntry('exam_new', './assets/js/exam/new.js')
    .addEntry('concept_list', './assets/js/concept/list.js')
    .addEntry('concept_new', './assets/js/concept/new.js')
    .addEntry('concept_edit', './assets/js/concept/edit.js')
    .addEntry('category_list', './assets/js/category/list.js')
    .addEntry('category_new', './assets/js/category/new.js')
    .addEntry('category_edit', './assets/js/category/edit.js')
    .addEntry('debtsFiles_list', './assets/js/debts/list.js')
    .addEntry('debtsFiles_upload', './assets/js/debts/upload.js')

//.addEntry('page2', './assets/js/page2.js')

// When enabled, Webpack "splits" your files into smaller pieces for greater optimization.
.splitEntryChunks()

// will require an extra script tag for runtime.js
// but, you probably want this, unless you're building a single-page app
.enableSingleRuntimeChunk()

/*
 * FEATURE CONFIG
 *
 * Enable & configure other features below. For a full
 * list of features, see:
 * https://symfony.com/doc/current/frontend.html#adding-more-features
 */
.cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(!Encore.isProduction())
    // enables hashed filenames (e.g. app.abc123.css)
    .enableVersioning(Encore.isProduction())

.configureBabel((config) => {
    config.plugins.push('@babel/plugin-proposal-class-properties');
})

// enables @babel/preset-env polyfills
.configureBabelPresetEnv((config) => {
    config.useBuiltIns = 'usage';
    config.corejs = 3;
})

// enables Sass/SCSS support
.enableSassLoader()

// uncomment if you use TypeScript
//.enableTypeScriptLoader()

// uncomment to get integrity="..." attributes on your script & link tags
// requires WebpackEncoreBundle 1.4 or higher
//.enableIntegrityHashes(Encore.isProduction())

// uncomment if you're having problems with a jQuery plugin
.autoProvidejQuery()

// uncomment if you use API Platform Admin (composer req api-admin)
//.enableReactPreset()
//.addEntry('admin', './assets/js/admin.js')
.copyFiles({
    from: './assets/images',
    to: 'images/[path][name].[hash:8].[ext]'
});

module.exports = Encore.getWebpackConfig();