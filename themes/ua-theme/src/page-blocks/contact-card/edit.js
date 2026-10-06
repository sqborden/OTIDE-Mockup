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
  const { TextControl, Button, Spinner, SelectControl, ToggleControl, BaseControl, Panel, PanelBody } = wp.components;
  const { useState, useEffect } = wp.element;
  import LinkPanel from '../../elements/link-panel';
  import { __ } from '@wordpress/i18n';
  import './editor.css';

  export default function Edit({ attributes, setAttributes }) {
    const { title, headingLevel, subtitle, url, opensInNewTab, showImage, mediaId, img, email, phone, location, website, textAlignment, isLandscape, personTags, selectedPostType, selectedPost, imageToggle, tagsToggle, emailToggle, phoneToggle, locationToggle, websiteToggle, linkToggle, descriptionToggle } = attributes;
    const blockProps = useBlockProps({className: 'ua-block ua_minerva'});
    const ALLOWED_BLOCKS = [ 'core/list', 'core/paragraph' ];
    const [posts, setPosts] = useState([]);
    const [isLoadingPosts, setIsLoadingPosts] = useState(false);
    const [isLandscapeCSS, setIsLandscapeCSS] = useState(() => {
      if(isLandscape){
        return ' ua_card--landscape'
      }else{
        return ''
      }
    })
    const [selectedPostId, SetselectedPostId] = useState(()=>{
      if(selectedPost){
        return selectedPost.id;
      }else{
        return 0;
      }
    })

    useEffect(() => {
        if (selectedPostType) {
            const controller = new AbortController();
            let cancelled = false;

            const fetchAllPosts = async () => {
                setIsLoadingPosts(true);
                let page = 1;
                let allPosts = [];
                let totalPages = 1;

                try {
                    do {
                        const response = await wp.apiFetch({
                            path: `/wp/v2/directory/?per_page=100&page=${page}&orderby=title&order=asc`,
                            parse: false,
                            signal: controller.signal,
                        });
                        totalPages = parseInt(response.headers.get('X-WP-TotalPages'), 10) || 1;
                        const data = await response.json();
                        allPosts = allPosts.concat(data);
                        page++;
                    } while (page <= totalPages);

                    if (!cancelled) {
                        setPosts(allPosts);

                        if (selectedPostId) {
                            handlePostSelect(selectedPostId, allPosts);
                        }
                    }
                } catch (error) {
                    if (!cancelled) {
                        console.error('Error fetching directory posts:', error);
                    }
                } finally {
                    if (!cancelled) {
                        setIsLoadingPosts(false);
                    }
                }
            };

            fetchAllPosts();

            return () => {
                cancelled = true;
                controller.abort();
            };
        }
    }, []);

    const handlePostSelect = (postId, initialPosts = false) => {
        if(postId == 0){
            setAttributes({ selectedPost: '' });
            SetselectedPostId(0)
        } else {
            let post;
            if(initialPosts) {
              post = initialPosts.find((post) => post.id == postId);
            } else {
              post = posts.find((post) => post.id == postId);
            }

            setAttributes({ selectedPost: post });
            SetselectedPostId(postId)

            const fetchTagNames = async (tagIds) => {
                try {
                    const siteUrl = get_site_url.siteUrl;
                    const tagNames = await Promise.all(tagIds.map(async (tagId) => {
                    const response = await fetch(`${siteUrl}/wp-json/wp/v2/directory_tag/${tagId}`);
                    const data = await response.json();
                    const tagArray = [{'name':data.name}, {'link': data.link}];
                    return tagArray;

                  }));
                  setAttributes({personTags: tagNames})
                } catch (error) {
                  console.error('Error fetching tag data:', error);
                }
              };

              if(post) {
                fetchTagNames(post.directory_tag);
              }
        }
    };

    const handleLandscape = (value) => {
      if(value){
        setAttributes({ isLandscape: true })
        setIsLandscapeCSS(' ua_card--landscape')
      }else{
        setAttributes({ isLandscape: false })
        setIsLandscapeCSS('')
      }
    }
    return (
      <>
    <div {...blockProps}>
      {selectedPost ? (
            <>
            <div className="ua_component_wrapper undefined ua_contact-card ua_presence--subtle">
                <article className={`ua_card `+isLandscapeCSS}>
                <div className="ua_card_content-wrapper">
                    <h3 className=" ua_card_title">{selectedPost.title.rendered}</h3>
                    <span className="ua_card_subtitle">{selectedPost.subtitle}</span>
                {(tagsToggle) ?
                    <div className="ua_component_wrapper">
                    <ul className="ua_contact-card_info">
                    {personTags.map((item) => (
                        <li key={item[1].link}><a href={item[1].link} rel="tag">{item[0].name}</a></li>
                    ))
                    }
                    </ul>
                </div>
                : '' }

                    {(descriptionToggle && selectedPost.excerpt?.rendered) ?
                        <div className="ua_contact-card_content ua_layout--flow-half" dangerouslySetInnerHTML={{ __html: selectedPost.excerpt.rendered }} />
                    : '' }
                    <ul className="ua_contact-card_info">
                    {((emailToggle) && (selectedPost.email != '')) ?
                        <li key={`mailto:`+selectedPost.email}><a href={`mailto:`+selectedPost.email} rel="email">
                            <span className="fa fa-envelope" aria-hidden="true"></span> {selectedPost.email}</a>
                        </li>
                    : '' }
                    {((phoneToggle) && (selectedPost.phone != '')) ?
                        <li key={`tel:`+selectedPost.phone}><a href={`tel:`+selectedPost.phone} rel="phone"><span className="fa fa-phone" aria-hidden="true">
                            </span> {selectedPost.phone}</a>
                        </li>
                    : '' }
                    {((locationToggle) && (selectedPost.location != '')) ?
                        <li key={selectedPost.location}><span className="fa fa-location-dot" aria-hidden="true"></span> {selectedPost.location}
                        </li>
                    : '' }
                    {((websiteToggle) && (selectedPost.website != '')) ?
                        <li key={selectedPost.website}><a href={selectedPost.website} rel="website">
                            <span className="fa fa-globe" aria-hidden="true"></span> {selectedPost.website}</a>
                        </li>
                    : '' }
                    </ul>
                </div>
                {((imageToggle) && (selectedPost.featured_image_url[0])) ?
                <div className="ua_card_image-wrapper">
                    <img src={selectedPost.featured_image_url[0]} alt="" />
                </div>
                : '' }
                </article>
            </div>
            </>
            ) : (
            <>
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
                    onChange={(val) => setAttributes({ subtitle: val })}
                    allowedFormats={["core/bold", "core/italic"]}
                    className="components-text-control__input"
                    value={subtitle}
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

                <BaseControl help={__("The card email")}>
                  <BaseControl.VisualLabel>{__("Email")}</BaseControl.VisualLabel>
                  <RichText
                    tagName="p"
                    multiline={false}
                    onChange={(val) => setAttributes({ email: val })}
                    allowedFormats={["core/bold", "core/italic"]}
                    className="components-text-control__input"
                    value={email}
                  />
                </BaseControl>

                <BaseControl help={__("The card phone")}>
                  <BaseControl.VisualLabel>{__("Phone")}</BaseControl.VisualLabel>
                  <RichText
                    tagName="p"
                    multiline={false}
                    onChange={(val) => setAttributes({ phone: val })}
                    allowedFormats={["core/bold", "core/italic"]}
                    className="components-text-control__input"
                    value={phone}
                  />
                </BaseControl>

                <BaseControl help={__("The card location")}>
                  <BaseControl.VisualLabel>{__("Location")}</BaseControl.VisualLabel>
                  <RichText
                    tagName="p"
                    multiline={false}
                    onChange={(val) => setAttributes({ location: val })}
                    allowedFormats={["core/bold", "core/italic"]}
                    className="components-text-control__input"
                    value={location}
                  />
                </BaseControl>

                <BaseControl help={__("The card website")}>
                  <BaseControl.VisualLabel>{__("Website")}</BaseControl.VisualLabel>
                  <RichText
                    tagName="p"
                    multiline={false}
                    onChange={(val) => setAttributes({ website: val })}
                    allowedFormats={["core/bold", "core/italic"]}
                    className="components-text-control__input"
                    value={website}
                  />
                </BaseControl>

                <BlockControls>
                  <AlignmentToolbar
                    value={textAlignment}
                    onChange={(val) => setAttributes({ textAlignment: val })}
                  />
                </BlockControls>
              </>
            )
        }
    </div>
        <InspectorControls>
          <>
            <Panel>
              <PanelBody title="Block settings" initialOpen={true}>

                <BaseControl help={__("Toggle the orientation of the card between vertical and horizontal")}>
                  <BaseControl.VisualLabel>
                    {__("Is Horizontal")}
                  </BaseControl.VisualLabel>

                  <ToggleControl
                    label={__("Is Horizontal")}
                    checked={isLandscape}
                    onChange={(val) => handleLandscape(val)}
                  />
                </BaseControl>

                <BaseControl help={__("The heading level for the title")}>
                  <BaseControl.VisualLabel>
                    {__("Heading level")}
                  </BaseControl.VisualLabel>

                  <SelectControl
                    value={headingLevel}
                    options={[
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
                <BaseControl help={__("Select a person for a contact card")}>
                <BaseControl.VisualLabel>
                  {__("Directory")}
                </BaseControl.VisualLabel>
                    {isLoadingPosts && <Spinner />}
                    <SelectControl
                        value={selectedPostId}
                        disabled={isLoadingPosts}
                        options={[{ label: isLoadingPosts ? __("Loading…") : __("Select Person"), value: "0" }].concat(posts.map((post) => ({
                          label: post.title.rendered,
                          value: post.id,
                      })))}
                        onChange={(value) => handlePostSelect(value)}
                    />
                </BaseControl>

                {selectedPost ? (
                  <>
                <BaseControl help={__("Toggle metadata for contact card")}>
                <BaseControl.VisualLabel>
                  {__("MetaData")}
                </BaseControl.VisualLabel>

                <ToggleControl
                  label={__("Profile Image")}
                  checked={imageToggle}
                  onChange={(val) => setAttributes({ imageToggle: val })}
                />

              <ToggleControl
                label={__("Tags")}
                checked={tagsToggle}
                onChange={(val) => setAttributes({ tagsToggle: val })}
              />

              <ToggleControl
                label={__("Description")}
                checked={descriptionToggle}
                onChange={(val) => setAttributes({ descriptionToggle: val })}
              />

              <ToggleControl
                label={__("Phone")}
                checked={phoneToggle}
                onChange={(val) => setAttributes({ phoneToggle: val })}
              />

              <ToggleControl
                label={__("Email")}
                checked={emailToggle}
                onChange={(val) => setAttributes({ emailToggle: val })}
              />

              <ToggleControl
                label={__("Location")}
                checked={locationToggle}
                onChange={(val) => setAttributes({ locationToggle: val })}
              />

              <ToggleControl
                label={__("Website")}
                checked={websiteToggle}
                onChange={(val) => setAttributes({ websiteToggle: val })}
              />

              <ToggleControl
                label={__("Link to Profile")}
                checked={linkToggle}
                onChange={(val) => setAttributes({ linkToggle: val })}
              />
            </BaseControl>
                </>
              ) : ''}
           </PanelBody>
            </Panel>
            { ( selectedPostId == 0) &&
            <LinkPanel
              attributes={attributes}
              setAttributes={setAttributes}
              enableOpensInNewTab={true}
            ></LinkPanel>
            }
          </>
        </InspectorControls>
      </>
    );
  }
