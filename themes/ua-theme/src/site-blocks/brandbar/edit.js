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

const { useBlockProps } = wp.blockEditor;

const Edit = () => {
  const blockProps = useBlockProps( { className: 'ua_brand-bar' } );

  return (
    <section { ...blockProps } >
      <div className="ua_brand-bar_content">
        <a href="https://ua.edu" className="ua_brand-bar_logo">
          <img src="https://assetfiles.ua.edu/brand/logos/UA_Wordmark-White.svg" alt="The University of Alabama" />
        </a>
        <a href="http://mybama.ua.edu/" className="ua_brand-bar_link">
          myBama
        </a>
      </div>
    </section>
  );
};

export default Edit;