const { useBlockProps, InnerBlocks, InspectorControls } = wp.blockEditor;
const { BaseControl, Panel, PanelBody, PanelRow, TextControl, SelectControl } = wp.components;
import { __ } from '@wordpress/i18n';

export default function Edit({ attributes, setAttributes }) {
  const { title, headingLevel, context } = attributes;
  const blockProps = useBlockProps({className: 'ua-block'});

  return (
    <>
      <div {...blockProps}>
        <BaseControl help={__('The Callout title')}>
          <BaseControl.VisualLabel>{__('Title')}</BaseControl.VisualLabel>

          <TextControl value={title} onChange={(val) => setAttributes({ title: val })} />
        </BaseControl>

        <BaseControl help={__("The Callout's content")}>
          <BaseControl.VisualLabel>{__('Content')}</BaseControl.VisualLabel>

          <InnerBlocks
            className="components-text-control__input"
            template={[['core/paragraph']]}
            allowedBlocks={[
              'core/paragraph',
              'core/list',
              'core/table',
              'core/heading',
              'core/quote',
              'core/image',
              'core/button',
            ]}
          />
        </BaseControl>
      </div>

      <InspectorControls>
        <Panel>
          <PanelBody title={__('Settings')} initialOpen={true}>
            <PanelRow className="components-panel__row__block">
              <SelectControl
                label={__('Heading level')}
                help={__("The heading level for the Callout's title")}
                value={headingLevel}
                options={[
                  { label: 'H1', value: 1 },
                  { label: 'H2', value: 2 },
                  { label: 'H3', value: 3 },
                  { label: 'H4', value: 4 },
                  { label: 'H5', value: 5 },
                ]}
                onChange={(value) => setAttributes({ headingLevel: value })}
              />

              <SelectControl
                label={__('Context')}
                help={__("The Callout's context")}
                value={context}
                options={[
                  { label: 'None', value: '' },
                  { label: 'Info', value: 'info' },
                  { label: 'Positive', value: 'positive' },
                  { label: 'Negative', value: 'negative' },
                ]}
                onChange={(value) => setAttributes({ context: value })}
              />
            </PanelRow>
          </PanelBody>
        </Panel>
      </InspectorControls>
    </>
  );
}
