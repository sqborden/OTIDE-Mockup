import { registerBlockType } from '@wordpress/blocks';

//import files for the current version of the block
import Edit from './edit';
import Save from './save';

import metadata from './block.json';

// register the block
registerBlockType(metadata.name, {
  example: {
    attributes: {
      message: 'Callout',
    },
  },

  edit: Edit,
  save: Save,
});
