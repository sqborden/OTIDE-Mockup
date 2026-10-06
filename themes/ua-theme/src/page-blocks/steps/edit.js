const { useBlockProps, useInnerBlocksProps, Inserter, InspectorControls } = wp.blockEditor;
const { Button, BaseControl, Panel, PanelBody, PanelRow, SelectControl} = wp.components;
import { __experimentalNumberControl as NumberControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

function MyButtonBlockAppender( { rootClientId } ) {
  return (
    <Inserter
      rootClientId={ rootClientId }
      renderToggle={ ( { onToggle, disabled } ) => (
        <Button
          onClick={ onToggle }
          disabled={ disabled }
          label="Add a Step"
          icon="plus"
          text="Add a Step"
        />
      )}
      isAppender
    />
  );
}

export default function Edit({  attributes, setAttributes, clientId }) {
  const { maxColumns, headingLevel } = attributes;
  const inlineStyles = maxColumns === 0 ? undefined : { '--grid-column-count': attributes.maxColumns };
  const blockProps = useBlockProps({
    className: 'ua-block ua_layout--grid', 
    style: inlineStyles
  });

  const innerBlocksProps = useInnerBlocksProps(blockProps, {
    allowedBlocks: ['ua-blocks/step'],
    renderAppender:  () => (
      <MyButtonBlockAppender rootClientId={ clientId } />
    ),
  });

  return (
    <>
      <BaseControl>
        <BaseControl.VisualLabel>{__('Steps')}</BaseControl.VisualLabel>
        <ul { ...innerBlocksProps }/>
      </BaseControl>
      <InspectorControls>
        <>
          <Panel>
            <PanelBody title="Max Columns" initialOpen={ true }>
              <PanelRow>
                <NumberControl
                  isShiftStepEnabled={ true }
                  onChange={ ( currentMaxCount ) => setAttributes({ maxColumns: parseInt(currentMaxCount) }) }
                  min= { 0 }
                  shiftStep={ 1 }
                  value={ attributes.maxColumns }
                />
              </PanelRow>
              <PanelRow>
                <BaseControl help={__("The heading level for the title")}>
                  <BaseControl.VisualLabel>{__("Heading level")}</BaseControl.VisualLabel>

                  <SelectControl
                    value={headingLevel}
                    options={[
                      { label: "H1", value: 1 },
                      { label: "H2", value: 2 },
                      { label: "H3", value: 3 },
                      { label: "H4", value: 4 },
                      { label: "H5", value: 5 },
                    ]}
                    onChange={(value) =>
                      setAttributes({ headingLevel: parseInt(value) })
                    }
                  />
                </BaseControl>
              </PanelRow>
            </PanelBody>
          </Panel>
        </>
      </InspectorControls>
    </>
  );
}
