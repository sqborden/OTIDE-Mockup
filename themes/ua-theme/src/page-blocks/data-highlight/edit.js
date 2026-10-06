const { useBlockProps, InspectorControls,
  BlockControls, AlignmentToolbar } = wp.blockEditor;
const { Panel, PanelBody, PanelRow, TextControl, ToggleControl, BaseControl } = wp.components;
import { __ } from '@wordpress/i18n';

export default function Edit({ attributes, setAttributes }) {
  const { title, description, lead, showLeadIn, textAlignment } = attributes;
  const blockProps = useBlockProps({className: 'ua-block'});

  return (
    <>
      <div {...blockProps}>
        {showLeadIn && (
          <BaseControl help={__('Lead-in information about the statistic')}>
            <BaseControl.VisualLabel>{__('Lead-in')}</BaseControl.VisualLabel>

            <TextControl value={lead} onChange={(value) => setAttributes({ lead: value })} />
          </BaseControl>
        )}

        <BaseControl help={__('The statistic to display')}>
          <BaseControl.VisualLabel>{__('Statistic')}</BaseControl.VisualLabel>

          <TextControl value={title} onChange={(value) => setAttributes({ title: value })} />
        </BaseControl>

        <BaseControl help={__('Information about the statistic')}>
          <BaseControl.VisualLabel>{__('Description')}</BaseControl.VisualLabel>
          <TextControl value={description} onChange={(value) => setAttributes({ description: value })} />
        </BaseControl>

        <BlockControls>
          <AlignmentToolbar
            value={textAlignment}
            onChange={(val) => setAttributes({ textAlignment: val })}
          />
        </BlockControls>
      </div>

      <InspectorControls>
        <Panel>
          <PanelBody title={__('Settings')} initialOpen={true}>
            <PanelRow>
              <ToggleControl
                label={__('Show a Lead-in')}
                checked={showLeadIn}
                onChange={(value) => {
                  setAttributes({ showLeadIn: value });
                }}
              />
            </PanelRow>
          </PanelBody>
        </Panel>
      </InspectorControls>
    </>
  );
}
