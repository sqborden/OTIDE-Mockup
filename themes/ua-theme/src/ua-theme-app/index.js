import domReady from '@wordpress/dom-ready';
const { createHigherOrderComponent } = wp.compose;
const { Fragment, useEffect } = wp.element;
const { useSelect } = wp.data;

const _config = {
	blocks: {
		// Sets the default block-supports for all core blocks
		// See: https://developer.wordpress.org/block-editor/reference-guides/block-api/block-supports/
		supports: {
			ariaLabel: true,
			defaultStylePicker: false,
			html: false,
			//Do not override the supports that take an object, otherwise rendering will break.
			//anchor: {},
			//color: {},
		},

		//See: https://developer.wordpress.org/block-editor/how-to-guides/block-tutorial/block-controls-toolbar-and-sidebar/#settings-sidebar
		removeSidebarSettings: [
			//'core/*',         //All core blocks
			//'sample/block',   //A specific block
			'core/paragraph',
      'core/buttons',
		],

		//See: https://developer.wordpress.org/block-editor/reference-guides/block-api/block-styles/
		removeSidebarAdvancedSettings: [
			//'core/*',         //All core blocks
			//'sample/block',   //A specific block
			'core/paragraph',
		],

		//See: https://developer.wordpress.org/block-editor/how-to-guides/block-tutorial/block-controls-toolbar-and-sidebar/#block-toolbar
		removeToolbarButtons: [
			//{ 'sample/block': [ 'aria-label of button to remove' ] },
	    { 'core/pullquote': [ 'Align text' ] },
      { 'core/table': [ 'Change column alignment' ] },
			{ 'core/buttons': [ 'Change vertical alignment' ] },
			{ 'core/button': [ 'Change vertical alignment', 'Align text' ] },
		],
	},

	//See: https://developer.wordpress.org/block-editor/how-to-guides/format-api/#overview
	rte: {
		removeToolbarMenus: [
			'core/code', 'core/text-color', 'core/image',
		],
	},
};

/*
* Initialize the app
*/
const initApp = () => {
	//Remove block styles from core blocks
	wp.hooks.addFilter(
		'blocks.registerBlockType',
		'ua-theme',
		removeCoreBlockStyles
	);

	//Set block supports from core blocks
	wp.hooks.addFilter(
		'blocks.registerBlockType',
		'ua-theme',
		setCoreBlockSupports,
	);

	//Add/delete options from a block's settings panel
	wp.hooks.addFilter(
		'editor.BlockEdit',
		'ua-theme',
		updateBlockEditControls
	);
};

/*
* Remove block styles from core blocks
*/
const removeCoreBlockStyles = ( settings, name ) => {
	if ( name.substring( 0, 5 ) === 'core/' ) {
		settings.styles = [];
	}

	return settings;
};

/*
* Set block supports for core blocks
*/
const setCoreBlockSupports = ( settings, name ) => {
	if ( name.substring( 0, 5 ) === 'core/' ) {
		if ( typeof ( settings.supports ) !== 'undefined' ) {
			Object.assign( settings.supports, _config.blocks.supports );
		}
	}

	return settings;
};

/*
* Removes menu options (formatTypes) from rich text editor
*/
const removeRteToolbarMenus = function() {
	//Possible format options: console.debug( wp.data.select( 'core/rich-text' ).getFormatTypes() );
	_config.rte.removeToolbarMenus.forEach( ( formatType ) => {
 		wp.richText.unregisterFormatType( formatType );
	} );
};

/*
* Handles making changes to the block editor
*/
const updateBlockEditControls =
  createHigherOrderComponent( ( BlockEdit ) => {
  	return ( props ) => {
  		const globalSettings = null; //<InspectorControls><PanelBody>Place Global Settings Here</PanelBody></InspectorControls>;

  		const selectedBlock = useSelect( ( select ) => {
  			return select( 'core/block-editor' ).getSelectedBlock();
  		}, [] );

  		useEffect( () => {
  			if ( selectedBlock !== null ) {
  				const blockTypeName = props.name.replace( /[\W_]+/g, '-' );

  				const waitTimeForRender = 50;

  				setTimeout( function() {
  					//remove sidebar panel settings
  					const sidebarPanels = document.querySelectorAll( '.interface-interface-skeleton__sidebar .components-panel__body:not(.block-editor-block-inspector__advanced)' );

  					if ( sidebarPanels !== null ) {
  						sidebarPanels.forEach( ( sidebarPanel ) => {
  							sidebarPanel.dataset.blockType = blockTypeName; //in case we need a specific selector

  							if ( _config.blocks.removeSidebarSettings.includes( selectedBlock.name ) ||
                ( selectedBlock.name.substring( 0, 5 ) === 'core/' && _config.blocks.removeSidebarSettings.includes( 'core/*' ) )
  							) {
  								sidebarPanel.style = 'display: none';
  							} else {
  								sidebarPanel.style = '';
  							}
  						} );
  					}

  					//remove sidebar panel advanced settings
  					const sidebarPanelsAdvanced = document.querySelectorAll( '.interface-interface-skeleton__sidebar .components-panel__body.block-editor-block-inspector__advanced' );
  					if ( sidebarPanelsAdvanced !== null ) {
  						sidebarPanelsAdvanced.forEach( ( sidebarPanelAdvanced ) => {
  							sidebarPanelAdvanced.dataset.blockType = blockTypeName; //in case we need a specific selector

  							if ( _config.blocks.removeSidebarAdvancedSettings.includes( selectedBlock.name ) ||
                            ( selectedBlock.name.substring( 0, 5 ) === 'core/' && _config.blocks.removeSidebarAdvancedSettings.includes( 'core/*' ) )
  							) {
  								sidebarPanelAdvanced.style = 'display: none;';
  							} else {
  								sidebarPanelAdvanced.style = '';
  							}
  						} );
  					}
  				}, waitTimeForRender ); //give panel time to render

  				//remove toolbar buttons
      		_config.blocks.removeToolbarButtons.forEach( ( toolbarButton ) => {
  					const blockTypes = Object.keys( toolbarButton );

  					blockTypes.forEach( ( blockType ) => {
  						if ( blockType === selectedBlock.name ) {
                //hide toolbar
  							waitForElement( '.block-editor-block-contextual-toolbar' ).then( ( element ) => {
  								element.style = 'display: none';
          			} );

  							//reset the toolbar
  							setTimeout( function() {
  								const toolbarButtons = document.querySelectorAll( '.components-toolbar-group .block-editor-block-toolbar__slot button' );
  								if ( toolbarButtons !== null ) {
  									toolbarButtons.forEach( ( button ) => {
  										button.hidden = false;
  										button.style = '';
  									} );
  								}
  							}, waitTimeForRender );

  							//hide the buttons matching the aria label we are looking for
  							setTimeout( function() {
  								toolbarButton[ blockType ].forEach( ( ariaLabel ) => {
  									const toolbarButtons = document.querySelectorAll( '.components-toolbar-group .block-editor-block-toolbar__slot button' );
               			if ( toolbarButtons !== null ) {
  										toolbarButtons.forEach( ( button ) => {

                        //See: https://developer.mozilla.org/en-US/docs/Web/API/Element/ariaLabel
  											if ( button.getAttribute( 'aria-label' ) === ariaLabel ) {
  												button.hidden = true;
  												button.style = 'display: none';
  											}
  										} );
  									}
  								} );
  							}, waitTimeForRender + 10 ); //give toolbar time to render

  							setTimeout( function() {
  								//hide empty toolbar groups
  								const toolbarGroups = document.querySelectorAll( '.block-editor-block-contextual-toolbar .components-toolbar-group' );
  								toolbarGroups.forEach( ( toolbarGroup ) => {
  									if ( toolbarGroup.querySelector( 'button:not([hidden])' ) === null ) {
  										toolbarGroup.style = 'display: none';
  									}
  								} );
  							}, waitTimeForRender + 20 );

  							//make toolbar visible again
  							setTimeout( function() {
  								const toolbar = document.querySelector( '.block-editor-block-contextual-toolbar' );
  								if ( toolbar !== null ) {
  									toolbar.style = '';
  								}
  							}, waitTimeForRender + 30 );
  						}
  					} );
  				} );
  			}
  		}, [ selectedBlock ] );

  		return (
	      <Fragment>{ globalSettings }<BlockEdit { ...props } /></Fragment>
  		);
  	};
  }, 'ua-theme' );

//See: https://stackoverflow.com/questions/5525071/how-to-wait-until-an-element-exists
const waitForElement = ( selector ) => {
	return new Promise( ( resolve ) => {
		if ( document.querySelector( selector ) ) {
			return resolve( document.querySelector( selector ) );
		}

		const observer = new MutationObserver( ( mutations ) => {
			if ( document.querySelector( selector ) ) {
				resolve( document.querySelector( selector ) );
				observer.disconnect();
			}
		} );

		observer.observe( document.body, {
			childList: true,
			subtree: true,
		} );
	} );
};

initApp();

domReady( function() {
	//Remove selected menu options from Rich Text Editor toolbar
	removeRteToolbarMenus();

	wp.richText.registerFormatType( 'ua-theme/accent-text', {
		title: 'Accent Text',
		tagName: 'span',
		className: 'ua_accent-text',
		edit( { isActive, onChange, value } ) {
			const selectedBlock = wp.data.select( 'core/block-editor' ).getSelectedBlock();
			if ( ! selectedBlock || selectedBlock.name !== 'core/heading' ) {
				return null;
			}
			return wp.element.createElement(
				wp.blockEditor.RichTextToolbarButton,
				{
					icon: 'admin-customizer',
					title: 'Accent Text',
					isActive,
					onClick() {
						onChange( wp.richText.toggleFormat( value, { type: 'ua-theme/accent-text' } ) );
					},
				}
			);
		},
	} );

	wp.blocks.unregisterBlockVariation( 'core/group', 'group-grid' );
  wp.blocks.unregisterBlockVariation("core/paragraph", "stretchy-paragraph");
  wp.blocks.unregisterBlockVariation("core/heading", "stretchy-heading");
} );
