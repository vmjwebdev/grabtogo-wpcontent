/**
 * WordPress depencencies
 */
import { __ } from "@wordpress/i18n";

/**
 * Internal dependencies
 */
import Edit from "./edit";
import Save from "./save";
import attributes from "./attributes";
import example from "./example";
import deprecated from "./deprecated";
import metadata from "../block.json";
import "./style.scss";
import { ebConditionalRegisterBlockType } from "@essential-blocks/controls";
import { ReactComponent as Icon } from "./icon.svg";

ebConditionalRegisterBlockType(metadata, {
    icon: Icon,
    attributes,
    keywords: [
        __("team", "essential-blocks"),
        __("member", "essential-blocks"),
        __("eb essential", "essential-blocks"),
    ],
    edit: Edit,
    save: Save,
    example,
    deprecated,
});
