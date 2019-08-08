/**
 * @license Copyright (c) 2003-2013, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.html or http://ckeditor.com/license
 */
alert('1');
CKEDITOR.editorConfig = function( config ) {
	// Define changes to default configuration here. For example:
	// config.language = 'fr';
	// config.uiColor = '#AADC6E';
    
config.protectedSource.push( /<script[\s\S]*?script>/g ); /* script tags */
config.allowedContent = true; /* all tags */

    //config.allowedContent     = Сimg[alt,dir,id,lang,longdesc,!src,title]{*}(*)Т;
    //config.extraAllowedContent = '*[id](*)'; // удал€ем '[id]',
?}; 


    // разрешить теги <style>
   // CKEDITOR.config.protectedSource.push(/<(style)[^>]*>.*<\/style>/ig);
    // разрешить теги <script>
    //CKEDITOR.config.protectedSource.push(/<(script)[^>]*>.*<\/script>/ig);
    // разрешить php-код
    //CKEDITOR.config.protectedSource.push(/<\?[\s\S]*?\?>/g);
    // разрешить любой код: <!--dev-->код писать вот тут<!--/dev-->
   // CKEDITOR.config.protectedSource.push(/<!--dev-->[\s\S]*<!--\/dev-->/g);

