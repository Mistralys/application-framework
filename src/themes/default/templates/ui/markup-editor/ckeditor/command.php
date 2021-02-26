<?php
/**
 * Template for the CKEditor initialization javascript
 * command, which is injected into the page for each
 * individual editor instance.
 * 
 * @package Application
 * @subpackage MarkupEditor
 * @see UI_MarkupEditor_CKEditor::_start()
 */

    /* @var $this UI_Page_Template */

    $selector = $this->getStringVar('selector');
    $buttons = $this->getArrayVar('buttons');
    $pluginNames = $this->getArrayVar('plugin-names');

    $this->ui->addStylesheet('markup-editor/ckeditor/styles.css');
    
?>
CKEditor.create( 
    document.querySelector('<?php echo $selector ?>'), 
    {
        language: {
            'ui': '<?php echo $this->getStringVar('language-ui') ?>',
            'content': '<?php echo $this->getStringVar('language-content') ?>'
        },
        plugins: [
            CKPlugins.<?php echo implode(','.PHP_EOL.str_repeat(' ', 12).'CKPlugins.', $pluginNames) ?>
        ],
        toolbar: {
            items: [
                '<?php echo implode("',".PHP_EOL.str_repeat(' ', 17)."'", $buttons) ?>'
        	]
        }
    } 
)
.then
( 
    editor => 
    {
        window.editor = editor;
        
        editor.model.document.on( 'change:data', () => 
        {
        	console.log('CKEditor | Text changed; updating form element.');
		    editor.updateSourceElement();
		});
    } 
)
.catch
( 
    error => 
    {
    	application.log('WYSIWYG Editor', 'Something has gone wrong in the editor.', 'error');
    	
    	console.error( error.stack );
    } 
);
