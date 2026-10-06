const {
  useBlockProps,
  InspectorControls,
  RichText,
  MediaUpload,
  MediaUploadCheck,
  InnerBlocks,
  BlockControls,
  AlignmentToolbar,
} = wp.blockEditor;
const { Button, Spinner, SelectControl, ToggleControl, BaseControl, Panel, PanelBody } = wp.components;
import LinkPanel from '../../elements/link-panel';
import { __ } from '@wordpress/i18n';
import './editor.css';

export default function Edit({ attributes, setAttributes }) {
  const { title, headingLevel, subTitle, url, opensInNewTab, showImage, mediaId, img, aspectRatio, textAlignment, isLandscape} = attributes;
  const blockProps = useBlockProps({className: 'ua-block'});
  const ALLOWED_BLOCKS = [ 'core/list', 'core/paragraph' ];

  return (
    <>
      <div {...blockProps}>
        <BaseControl help={__("The card title text")}>
          <BaseControl.VisualLabel>{__("Title")}</BaseControl.VisualLabel>

          <RichText
            tagName="p"
            multiline={false}
            onChange={(val) => setAttributes({ title: val })}
            allowedFormats={["core/bold", "core/italic"]}
            className="components-text-control__input"
            value={title}
          />
        </BaseControl>

        <BaseControl help={__("The card subtitle text")}>
          <BaseControl.VisualLabel>{__("SubTitle")}</BaseControl.VisualLabel>

          <RichText
            tagName="p"
            multiline={false}
            onChange={(val) => setAttributes({ subTitle: val })}
            allowedFormats={["core/bold", "core/italic"]}
            className="components-text-control__input"
            value={subTitle}
          />
        </BaseControl>

        <ToggleControl
          label={__("Show an image")}
          checked={showImage}
          onChange={(val) => setAttributes({ showImage: val })}
        />

        {showImage && (
          <>
            <BaseControl help={__("The card image")}>
              <BaseControl.VisualLabel>{__("Image")}</BaseControl.VisualLabel>

              <MediaUploadCheck>
                <MediaUpload
                  onSelect={(media) =>
                    setAttributes({
                      mediaId: media.id,
                      img: { src: media.url, alt: media.alt },
                    })
                  }
                  allowedTypes={["image"]}
                  value={mediaId}
                  render={({ open }) => (
                    <div>
                      {!mediaId && (
                        <Button variant="secondary" onClick={open}>
                          {__("Choose image")}
                        </Button>
                      )}
                      {!!mediaId && !img && <Spinner />}
                      {!!img && (
                        <Button variant="link" onClick={open}>
                          <img
                            src={img.src}
                            alt={img.alt}
                            className="ua-blocks__card-media-image"
                          />
                        </Button>
                      )}
                    </div>
                  )}
                />
              </MediaUploadCheck>
            </BaseControl>
          </>
        )}

        <BaseControl help={__("Information about the destination's content")}>
          <BaseControl.VisualLabel>{__("Description")}</BaseControl.VisualLabel>
          <InnerBlocks allowedBlocks={ALLOWED_BLOCKS} />
        </BaseControl>

        <BlockControls>
          <AlignmentToolbar
            value={textAlignment}
            onChange={(val) => setAttributes({ textAlignment: val })}
          />
        </BlockControls>
      </div>
      <InspectorControls>
        <>
          <Panel>
            <PanelBody title="Block settings" initialOpen={true}>
              { (!isLandscape) &&
                <SelectControl
                  label={__("Image Aspect ratio")}
                  value={aspectRatio}
                  options={[
                    { label: "Select", value: "" },
                    { label: "1/1", value: "1/1" },
                    { label: "2/3", value: "2/3" },
                    { label: "3/2", value: "3/2" },
                    { label: "16/9", value: "16/9" },
                  ]}
                  onChange={(value) => setAttributes({ aspectRatio: value })}
                /> 
              }

              <BaseControl help={__("Toggle the orientation of the card between vertical and horizontal")}>
                <BaseControl.VisualLabel>
                  {__("Is Horizontal")}
                </BaseControl.VisualLabel>

                <ToggleControl
                  label={__("Is Horizontal")}
                  checked={isLandscape}
                  onChange={(val) => setAttributes({ isLandscape: val })}
                />
              </BaseControl>

              <BaseControl help={__("The heading level for the title")}>
                <BaseControl.VisualLabel>
                  {__("Heading level")}
                </BaseControl.VisualLabel>

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
            </PanelBody>
          </Panel>
          <LinkPanel
            attributes={attributes}
            setAttributes={setAttributes}
            enableOpensInNewTab={true}
          ></LinkPanel>
        </>
      </InspectorControls>
    </>
  );
}
