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

    //config.allowedContent     = �img[alt,dir,id,lang,longdesc,!src,title]{*}(*)�;
    //config.extraAllowedContent = '*[id](*)'; // ������� '[id]',
?}; 


    // ��������� ���� <style>
   // CKEDITOR.config.protectedSource.push(/<(style)[^>]*>.*<\/style>/ig);
    // ��������� ���� <script>
    //CKEDITOR.config.protectedSource.push(/<(script)[^>]*>.*<\/script>/ig);
    // ��������� php-���
    //CKEDITOR.config.protectedSource.push(/<\?[\s\S]*?\?>/g);
    // ��������� ����� ���: <!--dev-->��� ������ ��� ���<!--/dev-->
   // CKEDITOR.config.protectedSource.push(/<!--dev-->[\s\S]*<!--\/dev-->/g);

