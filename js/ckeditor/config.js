    CKEDITOR.editorConfig = function( config ) {
        config.indentClasses = ["fa", "fa-eye","ul-grey", "ul-red", "text-red", "ul-content-red", "circle", "style-none", "decimal", "paragraph-portfolio-top", "ul-portfolio-top", "url-portfolio-top", "text-grey"];
        config.protectedSource.push(/<(style)[^>]*>.*<\/style>/ig);
        config.protectedSource.push(/<(script)[^>]*>.*<\/script>/ig);// ��������� ���� <script>
        config.protectedSource.push(/<(i)[^>]*>.*<\/i>/ig);// ��������� ���� <i>
        config.protectedSource.push(/<\?[\s\S]*?\?>/g);// ��������� php-���
        config.protectedSource.push(/<!--dev-->[\s\S]*<!--\/dev-->/g);
        config.allowedContent = true; /* all tags */
   };
   

CKEDITOR.config.indentClasses = ["bnt", "bnt_gray", "bnt_orange", "fa", "fa-eye"];
CKEDITOR.config.protectedSource.push(/<(style)[^>]*>.*<\/style>/ig);
CKEDITOR.config.protectedSource.push(/<(script)[^>]*>.*<\/script>/ig);// ��������� ���� <script>
CKEDITOR.config.protectedSource.push(/<(meta)[^>]*>.*<\/meta>/ig);// ��������� ���� <meta>
CKEDITOR.config.protectedSource.push(/<(i)[^>]*>.*<\/i>/ig);// ��������� ���� <i>
CKEDITOR.config.protectedSource.push(/<(b)[^>]*>.*<\/b>/ig);// ��������� ���� <i>
CKEDITOR.config.allowedContent = true; /* all tags */

