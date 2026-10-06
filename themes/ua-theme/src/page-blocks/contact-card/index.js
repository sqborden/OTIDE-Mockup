import { registerBlockType } from '@wordpress/blocks';
const { InnerBlocks, useBlockProps } = wp.blockEditor;

//import files for the current version of the block
import Edit from './edit';
import metadata from './block.json';

// register the block
registerBlockType(metadata.name, {
  example: {
    attributes: {
      message: 'Conatct Card',
    },
  },

  edit: Edit,
  save: (props) => {
      const { attributes } = props;
      const { selectedPost, personTags } = attributes;
      const blockProps = useBlockProps.save();
    return (
      <div {...blockProps}>
          {selectedPost ? (
            <div className="ua_component_wrapper undefined ua_contact-card ua_presence--subtle">
            <article className="ua_card ua_card--landscape">
            <div className="ua_card_content-wrapper">
                <h3 className=" ua_card_title">{selectedPost.title.rendered}</h3>
                <span className="ua_card_subtitle">{selectedPost.subtitle}</span>
                <div className="ua_component_wrapper">
                    <ul className="ua_tag-list ">
                    {personTags.map((item) => (
                        <li><a href={item[1].link} rel="tag">{item[0].name}</a></li>
                    ))
                    }
                    </ul>
                </div>
                <div className="ua_contact-card_content ua_layout--flow-half" dangerouslySetInnerHTML={{ __html: selectedPost.content.rendered }} />
                <ul className="ua_contact-card_info">
                    <li><a href={`mailto:`+selectedPost.email} rel="email">
                        <span className="fa fa-envelope" aria-hidden="true"></span>{selectedPost.email}</a>
                    </li>
                    <li><a href={`tel:`+selectedPost.phone} rel="phone"><span className="fa fa-phone" aria-hidden="true">
                        </span>{selectedPost.phone}</a>
                    </li>
                    <li><span className="fa fa-location-dot" aria-hidden="true"></span>{selectedPost.location}
                    </li>
                    <li><a href={selectedPost.website} rel="website">
                        <span className="fa fa-globe" aria-hidden="true"></span> {selectedPost.website}</a>
                    </li>
                </ul>
                <div className="ua_component_wrapper  ua_presence--subtle">
                    <a href="#" target="_blank" rel="noreferrer" className="ua_cta">View Profile</a>
                </div>
            </div>
            <div className="ua_card_image-wrapper">
                <img src={selectedPost.featured_image_url[0]} alt={selectedPost.title.rendered} />
            </div>
            </article>
        </div>
          ) : (
            <InnerBlocks.Content />
          )}
      </div>
  );
  },
});
