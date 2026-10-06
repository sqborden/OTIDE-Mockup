const { useBlockProps, InnerBlocks, InspectorControls } = wp.blockEditor;
const { BaseControl, Panel, PanelBody, PanelRow, TextControl, SelectControl } = wp.components;
import { __ } from '@wordpress/i18n';

export default function Edit({ attributes, setAttributes }) {
  const { qualifier, selector } = attributes;
  const blockProps = useBlockProps({className: 'ua-block'});

  return (
    <>
      <div {...blockProps}>
        <div class="ua_page-search">
          <div
            id="ua_page-search_input"
            class="ua_page-search_input"
            style={{ padding: "0.5em 0.75em" }}
          >
            Search this page
          </div>
        </div>
      </div>

      <InspectorControls>
        <Panel>
          <PanelBody title={__("Settings")} initialOpen={true}>
            <PanelRow className="components-panel__row__block">
              <TextControl
                label={__("CSS Qualifier")}
                value={qualifier}
                onChange={(value) => setAttributes({ qualifier: value })}
              />
            </PanelRow>
            <PanelRow className="components-panel__row__block">
              <TextControl
                label={__("CSS Selector")}
                value={selector}
                onChange={(value) => setAttributes({ selector: value })}
              />
            </PanelRow>
          </PanelBody>
        </Panel>
      </InspectorControls>
    </>
  );
}
