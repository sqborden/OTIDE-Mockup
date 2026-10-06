const { useBlockProps, useInnerBlocksProps, Inserter, InspectorControls } = wp.blockEditor;
const { Button, BaseControl, Panel, PanelBody, PanelRow} = wp.components;
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
          label="Add Link List Item"
          icon="plus"
          text="Add Link List Item"
        />
      )}
      isAppender
    />
  );
}

export default function Edit({  attributes, setAttributes, clientId }) {
  const { maxColumns } = attributes;
  const inlineStyles = maxColumns === 0 ? undefined : { '--grid-column-count': attributes.maxColumns };
  const blockProps = useBlockProps({
    className: 'ua-block ua_layout--grid', 
    style: inlineStyles
  });

  const innerBlocksProps = useInnerBlocksProps(blockProps, {
    allowedBlocks: ['ua-blocks/link-list-item'],
    renderAppender:  () => (
      <MyButtonBlockAppender rootClientId={ clientId } />
    ),
  });

  return (
    <>
        <BaseControl>
          <BaseControl.VisualLabel>{__('Link List')}</BaseControl.VisualLabel>
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
            </PanelBody>
          </Panel>
        </>
      </InspectorControls>
    </>
  );
}
