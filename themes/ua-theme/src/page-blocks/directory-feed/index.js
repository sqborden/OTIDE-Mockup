import { registerBlockType } from '@wordpress/blocks';
const { InnerBlocks } = wp.blockEditor;

//import files for the current version of the block
import Edit from './edit';
import metadata from './block.json';

// register the block
registerBlockType(metadata.name, {
  example: {
    attributes: {
      message: 'Directory Feed',
    },
  },

  edit: Edit,
  save() {
    return <InnerBlocks.Content />;
  },
});
