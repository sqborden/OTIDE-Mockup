import { registerBlockType } from '@wordpress/blocks';
import json from './block.json';
const { name } = json;

registerBlockType(name, {
  edit: () => null,
  save: () => null
});