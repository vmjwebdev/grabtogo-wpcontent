import { InnerBlocks } from "@wordpress/block-editor";
import {
    BlockProps
} from "@essential-blocks/controls";
export default function save({ attributes }) {
    const {
        classHook,
        blockId,
        layout,
        preset,
        verticalPreset,
        showDropdownIcon,
        navBtnType,
        hamburgerCloseIconAlign,
        navAlign,
        navVerticalAlign,
        version
    } = attributes;

    if (layout == "is-horizontal") {
        var layoutPreset = preset;
    } else {
        var layoutPreset = verticalPreset;
    }

    return (
        <BlockProps.Save attributes={attributes}>
            <div className={`eb-parent-wrapper eb-parent-${blockId} ${classHook}`}>
                <div
                    className={`${blockId} eb-advanced-navigation-wrapper ${version} ${layout} ${layoutPreset} ${layout == "is-horizontal" ? navAlign : navVerticalAlign
                        }${showDropdownIcon ? "" : "remove-dropdown-icon"} ${navBtnType === true ? "responsive-icon" : "responsive-text"
                        } ${hamburgerCloseIconAlign}`}
                >
                    <div className="eb-nav-contents">
                        <InnerBlocks.Content />
                    </div>
                </div>
            </div>
        </BlockProps.Save>
    );
}
