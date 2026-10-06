const {
  useBlockProps,
  InspectorControls,
  BlockControls,
  AlignmentToolbar,
} = wp.blockEditor;
const {
  Panel,
  PanelBody,
  SelectControl,
  ToggleControl,
} = wp.components;
import { __ } from '@wordpress/i18n';
import './editor.css';
const { useState, useEffect } = wp.element;

export default function Edit({ attributes, setAttributes }) {
  const { isRibbon, tagListType, textAlignment } = attributes;
  const blockProps = useBlockProps({className: `ua-block ua_minerva`});

  const [taxonomies, setTaxonomies] = useState([]);
 // Alignment to class
  const getAlignmentClass = (alignment) => {
    if (alignment === 'left') return 'ua_justify--start';
    if (alignment === 'right') return 'ua_justify--end';
    if (alignment === 'center') return 'ua_justify--center';
    return '';
  };
  const alignmentClass = getAlignmentClass(textAlignment);
  const [data, setData] = useState([]);

  useEffect(() => {
    tagsApiCall();
    taxonomyApiCall();
  }, []);

  useEffect(() => {
    if (tagListType) {
        tagsApiCall();
    }
  }, [tagListType]);

  const tagsApiCall = () => {
    let tagListTypeName = tagListType;
    if(tagListType === 'category'){
        tagListTypeName = 'categories';
    }else if( tagListType === 'post_tag'){
        tagListTypeName = 'tags';
    }
    // API call to fetch tags
      wp.apiFetch({ path: `/wp/v2/${tagListTypeName}/?per_page=100` }).then((data) => {
        setData(data);
      })
  }

  const taxonomyApiCall = () => {
    // API call to fetch taxonomies
  wp.apiFetch({ path: `/wp/v2/taxonomies?per_page=100` }).then((data) => {
    const filtered = Object.values(data).filter(
      (item) =>
        item.name.toLowerCase().includes('tags') ||
        item.name.toLowerCase().includes('categories') &&
        !item.name.toLowerCase().includes('pattern')
    );
    setTaxonomies(filtered);
  });
}

const decodeHtml = (html) => {
  const txt = document.createElement('textarea');
  txt.innerHTML = html;
  return txt.value;
}

  return (
    <>
    <div {...blockProps} style={{textAlign: textAlignment}}>
      <BlockControls>
        <AlignmentToolbar
          value={textAlignment}
          onChange={(val) => setAttributes({ textAlignment: val })}
        />
      </BlockControls>
      {}
      <ul className={`ua_tag-list ${alignmentClass}`}>
       {data && data.length > 0 && data.map((tag) => (
        <li key={`TagList_${tag.name}_${tag.id}`}>
          {tag.link ? (
            <a href={tag.link} target="_blank" rel="tag">{decodeHtml(tag.name)}</a>
          ) : (
            <span>{decodeHtml(tag.name)}</span>
        )}
        </li>
      ))}
      </ul>
    </div>

    <InspectorControls>
      <Panel>
        <PanelBody title={__('Tag List Settings', 'ua-theme')}>
            <SelectControl
                label='Select Taxonomy'
                value={tagListType}
                options={[                  
                    ...taxonomies.map((item) => ({
                    label: item.name,
                    value: item.slug,
                    })),
                ]}
                onChange={(value) => setAttributes({ tagListType: value })}
            />
            <ToggleControl
                label={'Ribbon Style'}
                checked={!!isRibbon}
                onChange={(value) => setAttributes({ isRibbon: value })}
            />
        </PanelBody>
      </Panel>
    </InspectorControls>
    </>
  );

}