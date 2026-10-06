const {
  useBlockProps,
  InspectorControls,
  BlockControls,
  AlignmentToolbar,
} = wp.blockEditor;
const {
  SelectControl,
  ToggleControl,
  BaseControl,
  Panel,
  PanelBody,
  __experimentalNumberControl,
  FormTokenField
} = wp.components;
import { __ } from '@wordpress/i18n';
import './editor.css';
const { useState, useEffect, useMemo } = wp.element;

// Honorific prefixes and degree/generational suffixes. Compared
// case-insensitively after stripping punctuation. Mirrored in
// inc/page-blocks/directory-feed.php — DIRECTORY_NAME_PREFIXES and
// DIRECTORY_NAME_SUFFIXES must stay in sync with these.
const NAME_PREFIXES = new Set([
  'dr', 'mr', 'mrs', 'ms', 'mx', 'prof', 'professor',
  'rev', 'reverend', 'fr', 'father', 'sr', 'hon', 'honorable',
]);

const NAME_SUFFIXES = new Set([
  'jr', 'sr', 'ii', 'iii', 'iv', 'v',
  'phd', 'md', 'jd', 'mba',
  'dds', 'dvm', 'edd', 'psyd',
  'ms', 'ma', 'bs', 'ba',
  'mph', 'mfa', 'llm',
  'esq', 'cpa', 'pe', 'rn',
]);

const stripNamePunct = (word) => word.replace(/[.,]/g, '').toLowerCase();

// Strip a title's comma tail, then peel leading prefix tokens and trailing
// suffix tokens. Always keeps at least one token if the input had any.
const normalizeNameTokens = (fullName) => {
  if (!fullName) return [];
  const withoutCommaTail = fullName.split(',')[0].trim();
  let tokens = withoutCommaTail.replace(/\s+/g, ' ').split(' ').filter(p => p.length > 0);

  while (tokens.length > 1 && NAME_PREFIXES.has(stripNamePunct(tokens[0]))) {
    tokens = tokens.slice(1);
  }
  while (tokens.length > 1 && NAME_SUFFIXES.has(stripNamePunct(tokens[tokens.length - 1]))) {
    tokens = tokens.slice(0, -1);
  }
  return tokens;
};

const sortPostsByLastName = (posts) => {
  return [...posts].sort((a, b) => {
    const tokensA = normalizeNameTokens(a.title.rendered);
    const tokensB = normalizeNameTokens(b.title.rendered);

    const lastA = (tokensA[tokensA.length - 1] || '').toLowerCase();
    const lastB = (tokensB[tokensB.length - 1] || '').toLowerCase();
    const cmp = lastA.localeCompare(lastB);
    if (cmp !== 0) return cmp;

    return tokensA.join(' ').toLowerCase().localeCompare(tokensB.join(' ').toLowerCase());
  });
};

const sortPostsByFirstName = (posts) => {
  return [...posts].sort((a, b) => {
    const tokensA = normalizeNameTokens(a.title.rendered);
    const tokensB = normalizeNameTokens(b.title.rendered);

    const firstA = (tokensA[0] || '').toLowerCase();
    const firstB = (tokensB[0] || '').toLowerCase();
    const cmp = firstA.localeCompare(firstB);
    if (cmp !== 0) return cmp;

    return tokensA.join(' ').toLowerCase().localeCompare(tokensB.join(' ').toLowerCase());
  });
};

// Pin posts with a positive integer order_index to the top, in ascending
// order. The non-indexed bucket preserves the incoming order so the
// caller's Sort By choice (first name or last name) is respected for the rest.
const pinPostsByOrderIndex = (posts) => {
  const postsWithIndex = [];
  const postsWithoutIndex = [];

  posts.forEach(post => {
    const orderIndex = post.order_index;

    if (orderIndex !== '' && !isNaN(orderIndex) && parseInt(orderIndex) > 0) {
      postsWithIndex.push(post);
    } else {
      postsWithoutIndex.push(post);
    }
  });

  postsWithIndex.sort((a, b) => {
    const indexA = parseInt(a.order_index || a._person_order_index || a.acf?.order_index || 0);
    const indexB = parseInt(b.order_index || b._person_order_index || b.acf?.order_index || 0);
    return indexA - indexB;
  });

  return [...postsWithIndex, ...postsWithoutIndex];
};

export default function Edit({ attributes, setAttributes }) {
  const {
    headingLevel,
    textAlignment,
    imageToggle,
    categoriesToggle,
    tagsToggle,
    excerptToggle,
    emailToggle,
    phoneToggle,
    locationToggle,
    websiteToggle,
    linkToggle,
    layout,
    maxColumns,
    orderBy,
    orderIndexOverride,
    category,
    tag,
    postPerPage,
    offset,
    maxPages,
    taxRelation } = attributes;
  const blockProps = useBlockProps({className: 'ua-block ua_minerva'});
  const [posts, setPosts] = useState([]);
  const [tags, setTags] = useState([]);
  const [categories, setCategories] = useState([]);
  const [categoriesNameArray, setCategoriesNameArray] = useState([]);
  const [tagsNameArray, setTagsNameArray] = useState([]);
  const [selectedCategories, setSelectedCategories ] = useState([]);
  const [selectedTags, setSelectedTags] = useState([]);
  const [categoryParam, setCategoryParam] = useState(() => {
    if (category == '') {
      return "";
    } else {
      return '&directory_category='+category;
    }
  })

  const [tagParam, setTagParam] = useState(() => {
    if (tag == '') {
      return "";
    } else {
      return '&directory_tag='+tag
    }
  });

  const [taxRelationParam, setTaxRelationParam] = useState(() => {
    return `&tax_relation=${taxRelation}`;
  });
  
  let inlineStyles =  { '--grid-column-count':1 };
  let landscape_css = 'ua_card--landscape';
  if(layout == "grid") {
     inlineStyles =  { '--grid-column-count':maxColumns };
     landscape_css = '';
  }

  useEffect(() => {
    // Migrate legacy saved blocks. "order_index" was once a Sort By choice
    // (now a separate toggle). "" once meant date-desc (now removed;
    // last_name is the default).
    if (orderBy === 'order_index') {
      setAttributes({ orderBy: 'last_name', orderIndexOverride: true });
    } else if (orderBy === '') {
      setAttributes({ orderBy: 'last_name' });
    }

    recordsApiCall();

    wp.apiFetch({ path: `/wp/v2/directory_category?per_page=100` }).then((data) => {
      setCategories(data);
      setCategoriesNameArray(data);
    });

    wp.apiFetch({ path: `/wp/v2/directory_tag?per_page=100` }).then((data) => {
      setTags(data);
      setTagsNameArray(data);
    });

    if(category != ''){
      const newArray1 = category.split(',');
      wp.apiFetch({ path: `/wp/v2/directory_category?include=${category}` }).then((data) => {
        setSelectedCategories(data.map(element => element.name));
      })
    }

    if(tag != ''){
      const newArray1 = tag.split(',');
      wp.apiFetch({ path: `/wp/v2/directory_tag?include=${tag}` }).then((data) => {
        setSelectedTags(data.map(element => element.name));
      })
    }

  }, []);

  useEffect(() => {
    recordsApiCall();
  }, [categoryParam, tagParam, taxRelationParam, orderBy, orderIndexOverride, postPerPage, offset]);

  const handleMultipleTaxonomy = (selectedTaxonomies, taxonomyName) => {

    (taxonomyName == 'category') ?  setSelectedCategories(selectedTaxonomies) :  setSelectedTags(selectedTaxonomies);
    if(!selectedTaxonomies?.length){
      setAttributes({[taxonomyName] : ''});
      (taxonomyName == 'category') ? setCategoryParam('') :  setTagParam('')
    }else{
      const taxonomyArray = taxonomyName === 'category' ? categoriesNameArray : tagsNameArray;
      const selectedSlugs = selectedTaxonomies.map(name => {
        const found = taxonomyArray.find(item => decodeHtmlEntities(item.name).toLowerCase() === decodeHtmlEntities(name).toLowerCase());
        return found ? found.slug : null;
      }).filter(Boolean);

      wp.apiFetch({ path: `/wp/v2/directory_${taxonomyName}?slug=${selectedSlugs}` }).then((data) => {
        let newArray = [];
        data.map((element) => {
          newArray.push(element.id)
        })
        setAttributes({[taxonomyName] : newArray.join(',')});
        if(taxonomyName == 'category'){
          setCategoryParam(`&directory_${taxonomyName}=`+newArray.join(','))
        }else{
          setTagParam(`&directory_${taxonomyName}=`+newArray.join(','))
        }
      })
    }
  }

  const handlePerPage = (perPage) => {
    perPage = parseInt(perPage);
    if (perPage < 1) {
      setAttributes({ postPerPage: 1 });
    } else if (perPage > 100) {
      setAttributes({ postPerPage: 100 });
    } else {
      setAttributes({ postPerPage: perPage });
    }
  }

  const handleOffset = (offsetValue) => {
    setAttributes({ offset: offsetValue });
  }

  const handleOrderBy = (orderByValue) => {
    setAttributes({ orderBy: orderByValue });
  }

  const handleTaxRelation = (value) => {
    setAttributes({ taxRelation: value });
    setTaxRelationParam(`&tax_relation=${value}`);
  }

  const recordsApiCall = async () => {
    const basePath = `/wp/v2/directory/?_embed=true&per_page=100${tagParam}${categoryParam}${taxRelationParam}`;
    try {
      const firstResponse = await wp.apiFetch({ path: `${basePath}&page=1`, parse: false });
      const totalPages = parseInt(firstResponse.headers.get('X-WP-TotalPages'), 10) || 1;
      const firstPageData = await firstResponse.json();

      let allData = firstPageData;
      if (totalPages > 1) {
        const rest = await Promise.all(
          Array.from({ length: totalPages - 1 }, (_, i) =>
            wp.apiFetch({ path: `${basePath}&page=${i + 2}` })
          )
        );
        allData = [...firstPageData, ...rest.flat()];
      }

      // last_name is also the fallback for any pre-migration legacy value
      // ("" or "order_index") that may briefly slip through on first render.
      const sorted = orderBy === 'first_name'
        ? sortPostsByFirstName(allData)
        : sortPostsByLastName(allData);
      const finalPosts = orderIndexOverride ? pinPostsByOrderIndex(sorted) : sorted;

      setPosts(finalPosts.slice(offset, offset + postPerPage));
    } catch (error) {
      console.error('Error fetching records:', error);
    }
  }

  const tagById = useMemo(
    () => Object.fromEntries((tags || []).map(t => [t.id, t])),
    [tags]
  );

  const categoryById = useMemo(
    () => Object.fromEntries((categories || []).map(c => [c.id, c])),
    [categories]
  );

  function decodeHtmlEntities(str) {
    const txt = document.createElement('textarea');
    txt.innerHTML = str;
    return txt.value;
  }
  return (
    <>
      <div {...blockProps}>
      <BlockControls>
        <AlignmentToolbar
          value={textAlignment}
          onChange={(val) => setAttributes({ textAlignment: val })}
        />
      </BlockControls>
      <div className="ua_component_wrapper alignwide">
      <div className="ua_layout--grid" style={inlineStyles}>
        {
          (posts.length>0) ?
            posts.map((post) => (
              <>
                <div className="ua_component_wrapper ua_contact-card ua_presence--subtle">
                  <article className={`ua_card `+landscape_css} >
                  {((imageToggle) && (post.featured_image_url[0])) ?
                    <div className="ua_card_image-wrapper">
                      { ( post &&
                          post._embedded &&
                          post._embedded['wp:featuredmedia'] &&
                          post._embedded['wp:featuredmedia'][0] &&
                          post._embedded['wp:featuredmedia'][0].media_details &&
                          post._embedded['wp:featuredmedia'][0].media_details.sizes &&
                          post._embedded['wp:featuredmedia'][0].media_details.sizes.medium &&
                          post._embedded['wp:featuredmedia'][0].media_details.sizes.medium.source_url ) ?

                        <img src={post._embedded['wp:featuredmedia'][0].media_details.sizes.medium.source_url} alt={post.title.rendered}/>
                      :
                        <img src={post.featured_image_url[0]} alt={post.title.rendered}/>
                      }                    
                    </div>
                  : '' }
                    <div className="ua_card_content-wrapper">
                      {((linkToggle) && (post.link != '')) ?
                        <h3 className=" ua_card_title" style={{color: '#9e1b32'}}>{post.title.rendered}</h3>
                      : <h3 className=" ua_card_title">{post.title.rendered}</h3>}
                      <span className="ua_card_subtitle">{post.subtitle}</span>
                      { ((categoriesToggle) && (post.directory_category && post.directory_category.length > 0)) ?
                      <ul className="ua_tag-list ">
                        {post.directory_category.map((catid) => {
                            const cat = categoryById[catid];
                            if (!cat) return null;
                            return (
                              <li key={catid}><a href={cat.link} dangerouslySetInnerHTML={{ __html: cat.name }} /></li>
                            );
                          })}
                      </ul>
                      : ''
                      }
                      { ((tagsToggle) && ((post.directory_tag).length > 0)) ?
                      <ul className="ua_tag-list ">
                        {post.directory_tag.map((tagid) => {
                            const tag = tagById[tagid];
                            if (!tag) return null;
                            return (
                              <li key={tagid}><a href={tag.link} dangerouslySetInnerHTML={{ __html: tag.name }} /></li>
                            );
                          })}
                      </ul>
                      : ''
                      }
                      {(excerptToggle && post.excerpt?.rendered) ?
                      <div className="ua_contact-card_content ua_layout--flow-half" dangerouslySetInnerHTML={{ __html: post.excerpt.rendered }} />
                      : ''
                      }
                      <ul className="ua_contact-card_info">
                      {((emailToggle) && (post.email != '')) ?
                        <li key={post.email}>
                          <a href={`mailto:`+post.email} rel="email">
                          <span className="fa fa-envelope" aria-hidden="true"></span> {post.email}
                          </a>
                        </li>
                      : '' }
                        {((phoneToggle) && (post.phone != '')) ?
                        <li key={post.phone+post.email}>
                          <a href={`tel:`+post.phone} rel="phone">
                            <span className="fa fa-phone" aria-hidden="true"></span> {post.phone}
                          </a>
                        </li>
                        : '' }
                      {((locationToggle) && (post.location != '')) ?
                        <li key={post.location+post.website}>
                          <span className="fa fa-location-dot" aria-hidden="true"></span> {post.location}
                        </li>
                      : '' }
                    {((websiteToggle) && (post.website != '')) ?
                        <li key={post.website}>
                          <a href={post.website} rel="website">
                            <span className="fa fa-globe" aria-hidden="true"></span> {post.website}
                          </a>
                        </li>
                      : '' }
                      </ul>
                    </div>
                  </article>
                </div>
              </>
            ))
          : ('No records available')
        }
      </div>
      </div>
      </div>

      <InspectorControls>
        <>
          <Panel>
            <PanelBody title="Block settings" initialOpen={true}>
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
              <SelectControl
                label={'Layout'}
                value={layout}
                options={[
                  { label: "List", value: "list" },
                  { label: "Grid", value: "grid" },
                ]}
                onChange={(value) => setAttributes({ layout: value })}
              />
              { layout == "grid" ?
              <__experimentalNumberControl
                label={"Max Columns"}
                  isShiftStepEnabled={ true }
                  onChange={ ( currentMaxCount ) => setAttributes({ maxColumns: parseInt(currentMaxCount) }) }
                  min= { 0 }
                  shiftStep={ 1 }
                  value={ attributes.maxColumns }
                />
              : '' }

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
                label={__("Categories")}
                checked={categoriesToggle}
                onChange={(val) => setAttributes({ categoriesToggle: val })}
                />

                <ToggleControl
                label={__("Tags")}
                checked={tagsToggle}
                onChange={(val) => setAttributes({ tagsToggle: val })}
                />

                <ToggleControl
                label={__("Phone")}
                checked={phoneToggle}
                onChange={(val) => setAttributes({ phoneToggle: val })}
                />

                <ToggleControl
                label={__("Excerpt")}
                checked={excerptToggle}
                onChange={(val) => setAttributes({ excerptToggle: val })}
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
            </PanelBody>
          </Panel>
          <Panel>
            <PanelBody title="Sort By" initialOpen={true}>
              <SelectControl
                label=''
                value={orderBy}
                options={[
                  { label: 'First Name', value: 'first_name' },
                  { label: 'Last Name', value: 'last_name' },
                ]}
                onChange={(value) => handleOrderBy(value)}
              />
              <ToggleControl
                label={__("Pin by Order Index")}
                help={__("Pins posts with a numeric order index to the top, then falls back to the selected sort method.")}
                checked={orderIndexOverride}
                onChange={(value) => setAttributes({ orderIndexOverride: value })}
              />
            </PanelBody>
          </Panel>
          <Panel>
            <PanelBody title="Filters" initialOpen={true}>
            <BaseControl>
              <SelectControl
                label={__("Filter Logic")}
                value={taxRelation}
                options={[
                  { label: "Any tag AND any category", value: "AND_ANY" },
                  { label: "All tags AND all categories", value: "AND" },
                  { label: "Any tag OR any category", value: "OR" },
                ]}
                onChange={(value) => handleTaxRelation(value)}
              />
              <BaseControl.VisualLabel>
                {__("Category")}
              </BaseControl.VisualLabel>
              <FormTokenField
                label=''
                onChange={(values) => handleMultipleTaxonomy(values, 'category')}
                suggestions={categoriesNameArray.map((cat) => decodeHtmlEntities(cat.name))}
                value={selectedCategories.map(decodeHtmlEntities)}
              />
              <BaseControl.VisualLabel>
                {__("Tag")}
              </BaseControl.VisualLabel>
              <FormTokenField
                label=''
                onChange={(values) => handleMultipleTaxonomy(values, 'tag')}
                suggestions={tagsNameArray.map((tag) => decodeHtmlEntities(tag.name))}
                value={selectedTags.map(decodeHtmlEntities)}
              />
            </BaseControl>
            </PanelBody>
          </Panel>
          <Panel>
            <PanelBody title="Display" initialOpen={true}>
            <BaseControl >
              <BaseControl.VisualLabel>
                {__("Post Per Page")}
              </BaseControl.VisualLabel>
              <__experimentalNumberControl
                isShiftStepEnabled={ true }
                onChange={ ( currentperPage ) => handlePerPage(parseInt(currentperPage)) }
                min = { 1 }
                max = { 100 }
                shiftStep = { 1 }
                value = { attributes.postPerPage }
              />
            </BaseControl>
            <BaseControl>
            <BaseControl.VisualLabel>
              {__("Offset")}
              </BaseControl.VisualLabel>
              <__experimentalNumberControl
                isShiftStepEnabled={ true }
                onChange={ ( currentOffset ) => handleOffset(parseInt(currentOffset))}
                min= { 0 }
                shiftStep={ 1 }
                value={ attributes.offset }
              />
            </BaseControl>
            <BaseControl help={__("Limit the pages you want to show, even if the query has more results. To show all pages use 0 (zero)")}>
            <BaseControl.VisualLabel>
                {__("Max Pages")}
              </BaseControl.VisualLabel>
              <__experimentalNumberControl
                isShiftStepEnabled={ true }
                onChange={ ( currentmaxPages ) => setAttributes({maxPages: parseInt(currentmaxPages)})}
                min= { 0 }
                shiftStep={ 1 }
                value={ attributes.maxPages }
              />
            </BaseControl>
            </PanelBody>
          </Panel>
        </>
      </InspectorControls>
    </>
  );
}
