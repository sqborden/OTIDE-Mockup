const { useState } = wp.element;
const { useBlockProps, RichText, InspectorControls } = wp.blockEditor;
const { Panel, PanelBody, PanelRow, TextControl, ToggleControl, BaseControl } = wp.components;
import LinkPanel from '../../elements/link-panel';
import { __ } from '@wordpress/i18n';
import './editor.css';

export default function Edit({ attributes, setAttributes }) {
  const { title, url, opensInNewTab, icon, iconAltText } = attributes;
  const [enableIcon, setEnableIcon] = useState(icon !== '');
  const blockProps = useBlockProps({ className: 'ua-block ua-blocks__list-item'});
  
  return (
    <>
      <div {...blockProps}>
        <BaseControl help={__('The link list item title text')}>
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
      </div>
      <InspectorControls>
        <>
          <Panel>
            <PanelBody title={__('Settings')} initialOpen={true}>
              <PanelRow>
                <ToggleControl
                  label={__('Show an icon')}
                  checked={enableIcon}
                  onChange={() => {
                    setEnableIcon((state) => !state);
                  }}
                />
              </PanelRow>

              {enableIcon && (
                <>
                  <PanelRow><a href="https://fontawesome.com/search?o=r&s=solid&f=classic" target='_blank'>ICON LIST</a></PanelRow>
                  <PanelRow>
                    <TextControl
                      label={__('Icon name')}
                      value={icon}
                      onChange={(value) => setAttributes({ icon: value })}
                    />
                  </PanelRow>

                  <PanelRow>
                    <TextControl
                      label={__('Icon alt text')}
                      value={iconAltText}
                      onChange={(value) => setAttributes({ iconAltText: value })}
                    />
                  </PanelRow>
                </>
              )}
            </PanelBody>
          </Panel>
          <LinkPanel attributes={attributes} setAttributes={setAttributes} enableOpensInNewTab={true}></LinkPanel>
        </>
      </InspectorControls>
    </>
  );
}
