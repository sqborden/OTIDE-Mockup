import { registerBlockType } from '@wordpress/blocks';

//import files for the current version of the block
import Edit from './edit';
import metadata from './block.json';

// register the block
registerBlockType(metadata.name, {
  example: {
    attributes: {
      message: 'Tag List',
    },
  },

  edit: Edit,
  save() {
    return null;
  },
});
