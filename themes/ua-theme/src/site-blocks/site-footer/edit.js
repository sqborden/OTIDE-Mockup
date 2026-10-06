const { useBlockProps, InnerBlocks } = wp.blockEditor;

const Edit = () => {
  const blockProps = useBlockProps( { className: 'ua_site-footer' } );

  return (
    <section { ...blockProps } >
      <div className="ua_site-footer_container">
        <div className="ua_site-footer_content">
          <InnerBlocks />
        </div>
        <div className="ua_site-footer_logos">
          <img
            className="ua_site-footer_ua-systems"
            alt="Part of the University of Alabama System"
            src="https://assetfiles.ua.edu/brand/logos/UA_System.svg"
          />
          <img
            className="ua_site-footer_denny-chimes"
            alt="Illustration of Denny Chimes"
            src="https://assetfiles.ua.edu/brand/logos/Denny_Chimes-Crimson.svg"
          />
        </div>
      </div>
    </section>
  );
};

export default Edit;