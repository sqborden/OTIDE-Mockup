const { useBlockProps, useInnerBlocksProps, InspectorControls } = wp.blockEditor;
const { BaseControl, Panel, PanelBody, PanelRow, SelectControl} = wp.components;
import { __ } from '@wordpress/i18n';
import './editor.css';
import { useEffect } from'react';

export default function Edit({context, setAttributes, attributes}) {
  const blockProps = useBlockProps({className: 'ua-block ua-blocks__grid-item'});
  const innerBlocksProps = useInnerBlocksProps(blockProps);
  const elementType = context['gridType'];
  const { gridType, verticalAlignment } = attributes;

  useEffect(() => {
    setAttributes({gridType:elementType})
  }, [gridType]);
  
  return (
    <>
      {
        elementType && (
          elementType === 'div' ? 
            <div { ...innerBlocksProps } />
          : 
            <li { ...innerBlocksProps } />
        )
      }
      <InspectorControls>
        <>
          <Panel>
            <PanelBody title="Vertical Alignment" initialOpen={ true }>
                <PanelRow>
                <SelectControl
                    value={verticalAlignment}
                    options={[
                      { label: "Top", value: "Top" },
                      { label: "Center", value: "Center" },
                      { label: "Bottom", value: "Bottom" },
                    ]}
                    onChange={(value) =>
                      setAttributes({ verticalAlignment: value })
                    }
                  />
                </PanelRow>
            </PanelBody>
          </Panel>
        </>
      </InspectorControls>

    </>

    
  );
}
