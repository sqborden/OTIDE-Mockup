const { useBlockProps, InspectorControls } = wp.blockEditor;
const { Panel, PanelBody, PanelRow, TextControl, ToggleControl, ButtonGroup, Button, SelectControl, __experimentalNumberControl: NumberControl } = wp.components;
import { __ } from '@wordpress/i18n';
import { useInstanceId } from '@wordpress/compose';

const WIDTH_PRESETS = [25, 50, 75, 100];

export default function Edit({ attributes, setAttributes }) {
  const { placeholder, buttonText, width, widthUnit, iconButton, showLabel, labelText } = attributes;
  const blockProps = useBlockProps({ className: 'ua-block' });
  const inputId = useInstanceId( Edit, 'ua-dir-search' );

  const isFullWidth = widthUnit === '%' && width >= 100;
  const previewWidth = isFullWidth ? undefined : `${width}${widthUnit}`;

  return (
    <>
      <div {...blockProps} style={{ width: previewWidth }}>
        {showLabel && (
          <label htmlFor={inputId} className="wp-block-search__label" style={{ display: 'block', marginBlockEnd: '0.25rem' }}>
            {labelText || __('Search')}
          </label>
        )}
        <form role="search" style={{ display: 'flex', gap: '0.5rem' }}>
          <input
            id={inputId}
            type="search"
            placeholder={placeholder}
            className='wp-block-search__input'
            disabled
            style={{ flex: '1' , border: '1px solid #8c8f94' }}
          />
          <button type="button" disabled className='wp-block-search__button wp-element-button'>
            {iconButton ? (
              <>
                <span className="fa fa-magnifying-glass" aria-hidden="true" />
                <span className="ua_visually-hidden">{buttonText || __('Search')}</span>
              </>
            ) : buttonText}
          </button>
        </form>
        <p style={{ marginBlockStart: '0.5rem', fontSize: '0.875rem', opacity: 0.6 }}>
          Directory Search — submits to the Directory Search page.
        </p>
      </div>

      <InspectorControls>
        <Panel>
          <PanelBody title={__('Settings')} initialOpen={true}>
            <PanelRow>
              <ToggleControl
                label={__('Show label')}
                checked={showLabel}
                onChange={(val) => setAttributes({ showLabel: val })}
              />
            </PanelRow>
            {showLabel && (
              <PanelRow>
                <TextControl
                  label={__('Label text')}
                  value={labelText}
                  onChange={(val) => setAttributes({ labelText: val })}
                />
              </PanelRow>
            )}

            <PanelRow>
              <ToggleControl
                label={__('Use icon button')}
                help={iconButton ? __('Button shows magnifying-glass icon.') : __('Button shows text label.')}
                checked={iconButton}
                onChange={(val) => setAttributes({ iconButton: val })}
              />
            </PanelRow>
            {!iconButton && (
                <TextControl
                  label={__('Button text')}
                  value={buttonText}
                  onChange={(val) => setAttributes({ buttonText: val })}
                />

            )}
            <PanelRow>
              <fieldset style={{ width: '100%' }}>
                <legend style={{ marginBlockEnd: '0.5rem' }}>{__('Width')}</legend>
                <ButtonGroup style={{ marginBlockEnd: '0.75rem' }}>
                  {WIDTH_PRESETS.map((preset) => (
                    <Button
                      key={preset}
                      variant={widthUnit === '%' && width === preset ? 'primary' : 'secondary'}
                      onClick={() => setAttributes({ width: preset, widthUnit: '%' })}
                    >
                      {preset + '%'}
                    </Button>
                  ))}
                </ButtonGroup>
                <div style={{ display: 'flex', gap: '0.5rem', alignItems: 'flex-end' }}>
                  <div style={{ flex: '1' }}>
                    <NumberControl
                      label={__('Custom width')}
                      value={width}
                      min={1}
                      onChange={(val) => setAttributes({ width: parseInt(val, 10) || 1 })}
                    />
                  </div>
                  <div style={{ flexShrink: 0 }}>
                    <SelectControl
                      label={__('Unit')}
                      value={widthUnit}
                      options={[
                        { label: '%', value: '%' },
                        { label: 'px', value: 'px' },
                      ]}
                      onChange={(val) => setAttributes({ widthUnit: val })}
                    />
                  </div>
                </div>
              </fieldset>
            </PanelRow>
            <PanelRow>
              <TextControl
                label={__('Placeholder text')}
                value={placeholder}
                onChange={(val) => setAttributes({ placeholder: val })}
              />
            </PanelRow>
            
            
          </PanelBody>
        </Panel>
      </InspectorControls>
    </>
  );
}
