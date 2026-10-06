import { registerBlockType } from '@wordpress/blocks';
import json from './block.json';
import Edit from './edit';
const { InnerBlocks } = wp.blockEditor;
const { name } = json;

registerBlockType(name, {
  edit: Edit,
  save: () => <InnerBlocks.Content />,
});