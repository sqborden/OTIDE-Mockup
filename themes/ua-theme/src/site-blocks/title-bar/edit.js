/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @param {Object}  props            Properties passed to the function.
 * @param {boolean} props.isSelected Block is selected or not.
 * @param {boolean} props.clientId   guid for the element.
 *
 * @return {WPElement} Element to render.
 */

const { InspectorControls, useBlockProps } = wp.blockEditor;
const { PanelBody, TextControl } = wp.components;
const { useEffect } = wp.element;

export default function Edit( { attributes, setAttributes } ) {
  useEffect( () => {
    // https://stackoverflow.com/a/71434399
    // Code here will run just like componentDidMount
    fetch( '/wp-json' ).then( resp => resp.json() ).then( data => {
      setAttributes( { siteTitle: data.name } );
    } ); 
  }, [] );

  const blockProps = useBlockProps( { className: 'ua_title-bar' } );  

  return (
    <>
      <InspectorControls>
        <PanelBody title="Additional Block Settings">
          <h3>Subtitle and Subtitle URL</h3>
          <TextControl 
              label="Subtitle"
              help="Enter the subtitle of this site if it has one."
              value={ attributes.subtitle }
              onChange={ value => setAttributes( { subtitle: value } ) }
              autoComplete="false"
              autoFill="false"
          />
          <TextControl 
              label="Subtitle URL"
              help="Enter the subtitle URL of this site if it has one."
              value={ attributes.subtitleURL }
              onChange={ value => setAttributes( { subtitleURL: value } ) }
          />
        </PanelBody>
      </InspectorControls>

      <section { ...blockProps } >
        <div className="ua_title-bar_content" id="UA_TitleBar_Content">
          <div className="ua_title-bar_title-group">
            <a href="/" className="ua_title-bar_name">
              {attributes.siteTitle}
            </a>
            {attributes.subtitle && attributes.subtitleURL ? (
              <a href={attributes.subtitleURL} className="ua_title-bar_subtitle">
                {attributes.subtitle}
              </a>
            ) : attributes.subtitle ? (
              <span className="ua_title-bar_subtitle">{attributes.subtitle}</span>
            ) : null}
          </div>
          <form id="UA_TitleSearch" action="/search" method="GET" className="ua_input-group ua_title-bar_search" role="search" aria-label="Sitewide">
            <label className="ua_visually-hidden" htmlFor="UA_TitleSearch_Input">
              Search This Site
            </label>
            <input type="search" role="searchbox" name="q" id="UA_TitleSearch_Input" />
            <button type="submit">
              <span className="fa fa-magnifying-glass" title="Submit"></span>
              <span className="ua_visually-hidden">Submit</span>
            </button>
          </form>
        </div>
      </section>
    </>
  );
};