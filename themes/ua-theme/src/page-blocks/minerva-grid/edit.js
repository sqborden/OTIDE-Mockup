const { useBlockProps, useInnerBlocksProps, Inserter,InspectorControls } = wp.blockEditor;
const { SelectControl, Button, Panel, PanelBody, PanelRow} = wp.components;
import { __experimentalNumberControl as NumberControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

function MyButtonBlockAppender( { rootClientId } ) {
  return (
    <Inserter
      rootClientId={ rootClientId }
      renderToggle={ ( { onToggle, disabled } ) => (
        <Button
          className="my-button-block-appender"
          onClick={ onToggle }
          disabled={ disabled }
          label="Add a Block"
          icon="plus"
          text="Add Grid Item"
        />
      )}
      isAppender
    />
  );
}

export default function Edit({ attributes, setAttributes, clientId }) {
  const { gridType, maxColumns } = attributes;
  const inlineStyles = maxColumns === 0 ? undefined : { '--grid-column-count': attributes.maxColumns };
  const blockProps = useBlockProps({
    className: 'ua-block ua_layout--grid', 
    style: inlineStyles
  });
  const innerBlocksProps = useInnerBlocksProps(blockProps,{
    allowedBlocks: ['ua-blocks/minerva-grid-item'],
    renderAppender:  () => (
      <MyButtonBlockAppender rootClientId={ clientId } />
    ),
  });

  return (
    <>
      <SelectControl
        label="Grid Type"
        value={ gridType }
        options={ [
            { label: 'Select', value: '' },
            { label: 'Ordered List', value: 'ol' },
            { label: 'Unordered List', value: 'ul' },
            { label: 'Arrangement', value: 'div' },
        ] }
        onChange={ ( value ) => setAttributes({ gridType: value })}
      />

      { gridType && (
          gridType === 'ul' ? (
              <ul { ...innerBlocksProps }/>
          ) : 
          gridType === 'ol' ? (
              <ol { ...innerBlocksProps }/>
          ) : 
          gridType === 'div' ? (
              <div { ...innerBlocksProps }/>
          ) : ''
      )}
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
            </PanelBody>
          </Panel>
        </>
      </InspectorControls>
    </>
  );
}
