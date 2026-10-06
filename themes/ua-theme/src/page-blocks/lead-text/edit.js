const { useBlockProps, RichText, InnerBlocks } = wp.blockEditor;
const { BaseControl } = wp.components;
import { __ } from '@wordpress/i18n';

export default function Edit({ attributes, setAttributes }) {
  const { text } = attributes;
  const blockProps = useBlockProps({className: 'ua-block'});

  return (
    <>
      <div {...blockProps}>
        <BaseControl help={__('The Lead Text')}>
          <BaseControl.VisualLabel>{__('Lead Text')}</BaseControl.VisualLabel>
          <RichText
            tagName="p"
            multiline={false}
            onChange={(val) => setAttributes({ text: val })}
            allowedFormats={['core/bold', 'core/italic']}
            className="components-text-control__input"
            value={text}
          />
        </BaseControl>
      </div>
    </>
  );
}
