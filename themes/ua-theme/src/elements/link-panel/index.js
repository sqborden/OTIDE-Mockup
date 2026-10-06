const { BaseControl, PanelBody, PanelRow } = wp.components;
import { __experimentalLinkControl as LinkControl } from '@wordpress/block-editor';

import { __ } from '@wordpress/i18n';

export default function LinkPanel({ attributes, setAttributes, enableOpensInNewTab, title, label, help }) {
  const { url, opensInNewTab } = attributes;

  if (title === undefined) {
    title = __('Link Settings');
  }

  if (label === undefined) {
    label = ''; //__( 'Link' );
  }

  const onChangeLink = (val) => {
    setAttributes({ url: val.url, opensInNewTab: val.opensInNewTab });
  };

  const onRemoveLink = (val) => {
    setAttributes({ url: '' });
  };

  return (
    <>
      <PanelBody title={title} initialOpen={true}>
        {enableOpensInNewTab && (
          <PanelRow>
            <BaseControl help={help}>
              <BaseControl.VisualLabel>{label}</BaseControl.VisualLabel>

              <LinkControl
                value={{ url, opensInNewTab }}
                onChange={onChangeLink}
                onRemove={onRemoveLink}
                settings={[
                  {
                    id: 'opensInNewTab',
                    title: __('Open in new tab', 'ua-blocks'),
                  },
                ]}
              />
            </BaseControl>
          </PanelRow>
        )}

        {!enableOpensInNewTab && (
          <PanelRow>
            <BaseControl help={help}>
              <BaseControl.VisualLabel>{label}</BaseControl.VisualLabel>

              <LinkControl
                label={label}
                value={{ url, opensInNewTab: false }}
                onChange={onChangeLink}
                onRemove={onRemoveLink}
                settings={[]}
              />
            </BaseControl>
          </PanelRow>
        )}
      </PanelBody>
    </>
  );
}
