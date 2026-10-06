const { SelectControl, CheckboxControl, RadioControl } = wp.components;
const { useSelect } = wp.data;
const { useEntityProp } = wp.coreData;
const { useBlockProps, useInnerBlocksProps } = wp.blockEditor;
const { useEffect, useState } = wp.element;
const { serialize } = wp.blocks;

const Edit = ({ setAttributes, attributes }) => {
  const blockProps = useBlockProps();
  const innerBlocksProps = useInnerBlocksProps( blockProps, { layout: { type: "constrained" }, } ); 

  const { postType, siteURL, editorBlocks } = useSelect( ( select ) => {
    const editorStore = select( 'core/editor' );
    const coreStore = select('core');
    return {
      editorBlocks: editorStore.getBlocks(),
      postType: editorStore.getCurrentPostType(),
      siteURL: coreStore.getSite() ? coreStore.getSite().url : ''
    };
  }, [] );

  const { menuOptions } = attributes;
  
  if( ! postType ) {
    return null;
  }
  
  const [meta, setMeta] = useEntityProp('postType', postType, 'meta');
  const { hero_blocks, hero, sidebar, sidebar_menu, sidebar_type, title_alignment, title_width } = meta;

  const dataBlock = editorBlocks.find(block => block.name === 'ua-theme/data');
  const [ hasHeroH1, setHasHeroH1 ] = useState(false);

  function findH1InBlocks(blocks) {
    for (const block of blocks) {
      if( block.name === 'core/heading' && block.attributes &&block.attributes.level === 1 ) {
        return true;
      }
      if (block.innerBlocks && block.innerBlocks.length > 0) {
        if (findH1InBlocks(block.innerBlocks)) {
          return true;
        }
      }
    }
    return false;
  };

  function useDebounce(value,delay) {
    const [debouncedValue, setDebouncedValue] = useState(value);
    useEffect(() => {
        const handler = setTimeout(() => {
          setDebouncedValue(value);
        }, delay);

        return () => {
          clearTimeout(handler);
        };
    }, [value, delay]);
    return debouncedValue;
  }

  const debouncedInnerBlocks = useDebounce(dataBlock?.innerBlocks, 300);
  useEffect(() => {
    if(!dataBlock) {
      return;
    };

    setHasHeroH1(findH1InBlocks(debouncedInnerBlocks));

    if(hero_blocks !== serialize(dataBlock.innerBlocks)) {
      setMeta({ ...meta, hero_blocks: serialize(dataBlock.innerBlocks) });
    }
  }, [debouncedInnerBlocks]);

  async function loadMenuItems() {
    const response = await fetch( siteURL + '/?rest_route=/ua-theme/v1/menus' );

    if ( ! response.ok ) {
      return;
    }

    const items = await response.json();
    let formatItems = [];
    
    items.forEach((item) => {
      const formatItem = { label: item.name, value: item.slug }
      formatItems.push( formatItem );
    });

    if (
      !menuOptions ||
      menuOptions.length !== formatItems.length ||
      formatItems.some((item, i) => !menuOptions[i] || menuOptions[i].value !== item.value)
    ) {
      setAttributes({ menuOptions: formatItems });
    }

    if (formatItems.length) {
      const menuSlugs = formatItems.map(item => item.value);
      if (!menuSlugs.includes(sidebar_menu)) {
        setMeta({ ...meta, sidebar_menu: formatItems[0].value });
      }
    }
  }
  
  useEffect(() => {
		loadMenuItems();
  }, [])
  return (
    <>
      <div { ...blockProps } >
        <h2>Page Settings</h2>
        <div className="data_flex">
          <CheckboxControl 
            label="Hero Content"
            help="Check this if you want to add custom content to the hero area. Please ensure your content includes an H1 heading in order to comply with accessibility standards. If Hero Content contains an H1, the default page title will be hidden"
            checked={ hero }
            onChange={ value => setMeta( { ...meta, hero: value } ) }
          />

          <CheckboxControl 
            label="Sidebar Content"
            help="Check this if you want to add a sidebar menu to this page."
            checked={ sidebar }
            onChange={ value => setMeta( { ...meta, sidebar: value } ) }
          />
        </div>

        { ( hero ) && (
            <div {...innerBlocksProps} className="is-layout-constrained hero-content"></div>
        ) }

        { ( !hero || !hasHeroH1 ) && (
          <div className="is-layout-constrained page-title-settings">
            <h3>Page Title Settings</h3>
            <div>
              <RadioControl
                label="Title Alignment"
                selected={ title_alignment }
                options={ [
                    { label: 'Left', value: 'left' },
                    { label: 'Center', value: 'center' },
                    { label: 'Right', value: 'right' }
                ] }
                onChange={ ( value ) => { 
                  setMeta( { ...meta, title_alignment: value } ); 
                } }
              />

              <RadioControl
                label="Title Width"
                selected={ title_width }
                options={ [
                    { label: 'Standard', value: 'standard' },
                    { label: 'Wide', value: 'wide' },
                ] }
                onChange={ ( value ) => { 
                  setMeta( { ...meta, title_width: value } ); 
                } }
              />
            </div>
          </div>
        ) }

        { ( sidebar ) && (
          <div className="is-layout-constrained">
            <h3>Sidebar Content</h3>
            <RadioControl
              label="Sidebar Type"
              selected={ sidebar_type }
              options={ [
                  { label: 'Menu Select', value: 'select' },
                  { label: 'Dynamic', value: 'dynamic' },
              ] }
              onChange={ ( value ) => { 
                setMeta( { ...meta, sidebar_type: value } ); 
              } }
              help="Either select from your created menus or populate sidebar links dynamically with the current page's parent, children, and siblings."
            />

            { ( menuOptions && menuOptions.length > 0 && sidebar_type === 'select' ) &&
            <div className="sidebar-content">
              <SelectControl 
                label="Sidebar Menu"
                value={ sidebar_menu }
                options={ menuOptions }
                onChange={ ( value ) => { 
                  setMeta( { ...meta, sidebar_menu: value } ); 
                } }
              />
            </div> }
            { ( menuOptions && menuOptions.length === 0 && sidebar_type === 'select' ) && 
              <p>No menus have been created to load in the sidebar area. Go to <a href={siteURL + "/wp-admin/nav-menus.php"}>this page</a> to create a menu.</p>
            }
          </div>
        ) }
      </div>
    </>
  );
};

export default Edit;