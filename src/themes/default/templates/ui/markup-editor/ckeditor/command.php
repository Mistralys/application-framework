<?php
/**
 * @package Application
 * @subpackage MarkupEditor
 * @see template_default_ui_markup_editor_ckeditor_command
 */

declare(strict_types=1);

/**
 * Template for the CKEditor initialization javascript
 * command, which is injected into the page for each
 * individual editor instance.
 *
 * For more details on the CKEditor build, see the
 * following folder:
 *
 * <code>themes/default/js/markup-editor/ckeditor</code>
 *
 * @package UserInterface
 * @subpackage Templates
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see UI_MarkupEditor_CKEditor::_start()
 */
class template_default_ui_markup_editor_ckeditor_command extends UI_Page_Template_Custom
{
    protected function generateOutput() : void
    {
        // Note: the CKEditor class is defined in the CKEditor build,
        // it is not the default class name.
    ?>
CKEditor.create(
    document.querySelector('<?php echo $this->selector ?>'),
    {
        language: {
            'ui': '<?php echo $this->getStringVar('language-ui') ?>',
            'content': '<?php echo $this->getStringVar('language-content') ?>'
        },
        additionalPlugins: [
            <?php
            // Add any plugins added via the addPlugin() method. Note that the
            // CKPlugins object is defined during the editor build process, and
            // contains references to the plugin classes.
            if(!empty($this->pluginNames)) {
                echo 'CKPlugins.'.implode(',' . PHP_EOL . str_repeat(' ', 12).'CKPlugins.', $this->pluginNames);
            }
            ?>
        ],
        toolbar: {
            items: [
                '<?php echo implode("',".PHP_EOL.str_repeat(' ', 17)."'", $this->buttons) ?>'
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
        <?php
    }

    protected string $selector;
    protected array $buttons;
    protected array $pluginNames;

    protected function preRender(): void
    {
        $this->selector = $this->getStringVar('selector');
        $this->buttons = $this->getArrayVar('buttons');
        $this->pluginNames = $this->getArrayVar('plugin-names');

        $this->ui->addStylesheet('markup-editor/ckeditor/styles.css');
    }
}
