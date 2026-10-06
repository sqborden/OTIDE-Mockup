const { useBlockProps, useInnerBlocksProps, RichText, InnerBlocks } = wp.blockEditor;
const { BaseControl } = wp.components;
import { __ } from '@wordpress/i18n';
import { useEffect } from'react';

export default function Edit({ context, attributes, setAttributes }) {
  const { title } = attributes;
  const blockProps = useBlockProps({className: 'ua-block'});
  const innerBlocksProps = useInnerBlocksProps(useBlockProps);
  const heading = context['headingLevel'];
  const { headingLevel } = attributes;

  useEffect(() => {
    setAttributes({headingLevel:heading})
  }, [heading]);
  
  return (
    <>
      <div {...blockProps}>
        <BaseControl help={__('The title of the step')}>
          <BaseControl.VisualLabel>{__('Title')}</BaseControl.VisualLabel>
          <RichText
            tagName="p"
            multiline={false}
            onChange={(val) => setAttributes({ title: val })}
            allowedFormats={['core/bold', 'core/italic']}
            className="components-text-control__input"
            value={title}
          />
        </BaseControl>

        <BaseControl help={__("The supporting description of the step")}>
          <BaseControl.VisualLabel>{__('Description')}</BaseControl.VisualLabel>
          <InnerBlocks/>
        </BaseControl>
      </div>
    </>
  );
}
