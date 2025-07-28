/**
 * WordPress dependencies
 */
import { __ } from "@wordpress/i18n";
import { memo } from "@wordpress/element";
import { InnerBlocks } from "@wordpress/block-editor";
import { select } from "@wordpress/data";
import {
    DynamicInputValueHandler,
    EBDisplayIcon,
    BlockProps,
    withBlockContext
 } from "@essential-blocks/controls";
import defaultAttributes from './attributes'
import Style from "./style";
import Inspector from "./inspector";

const Edit = (props) => {
    const {
        attributes,
        setAttributes,
        isSelected,
        clientId,
    } = props;
    const {
        resOption,
        blockId,
        blockMeta,
        classHook,
        //
        trigger,
        btnText,
        btnIcon,
        iconPosition,
        btnAlignment,
        btnType,
        triggerIcon,
        useCloseIcon,
        displayCloseIcon,
        closeBtnText,
    } = attributes;

    // you must declare this variable
    const enhancedProps = {
        ...props,
        blockPrefix: 'eb-popup',
        style: <Style {...props} />
    };

    const alignmentClass =
        "left" === btnAlignment
            ? " alignment-left"
            : "right" === btnAlignment
                ? " alignment-right"
                : " alignment-center";

    return (
        <>
            {isSelected && <Inspector {...props} />}
            <BlockProps.Edit {...enhancedProps}>
                <div className="eb-parent-wrapper">
                    <div
                        className={`${blockId} eb-popup-container`}
                        data-block-id={blockId}
                    >
                        <div
                            className={`eb-popup-btn-wrapper eb-parent-${blockId} ${classHook}`}
                        >
                            {"btn_click" === trigger && (
                                <>
                                    <div
                                        className={`eb-popup-button${alignmentClass}`}
                                    >
                                        <a className="eb-popup-button-anchor">
                                            {"button" === btnType && (
                                                <>
                                                    {btnIcon &&
                                                        "left" ===
                                                        iconPosition && (
                                                            <>
                                                                <EBDisplayIcon
                                                                    classNam={`eb-popup-button-icon eb-popup-button-icon-left`}
                                                                    icon={btnIcon}
                                                                />
                                                            </>
                                                        )}
                                                    <DynamicInputValueHandler
                                                        value={btnText}
                                                        className="eb-popup-button-text"
                                                        placeholder={__(
                                                            "Add Text..",
                                                            "essential-blocks"
                                                        )}
                                                        allowedFormats={[
                                                            "core/bold",
                                                            "core/italic",
                                                            "core/strikethrough",
                                                        ]}
                                                        onChange={(btnText) =>
                                                            setAttributes({
                                                                btnText,
                                                            })
                                                        }
                                                        readOnly={true}
                                                    />
                                                    {btnIcon &&
                                                        "right" ===
                                                        iconPosition && (
                                                            <>
                                                                <EBDisplayIcon
                                                                    classNam={`eb-popup-button-icon eb-popup-button-icon-right`}
                                                                    icon={btnIcon}
                                                                />
                                                            </>
                                                        )}
                                                </>
                                            )}
                                            {"icon" === btnType && (
                                                <>
                                                    <EBDisplayIcon classNam={`eb-popup-icon`} icon={triggerIcon} />
                                                </>
                                            )}
                                        </a>
                                    </div>
                                </>
                            )}
                        </div>
                        <div className="eb-popup-before-content">
                            <p>
                                <strong>Essential Blocks Popup:</strong> Design
                                your popup content below using blocks
                            </p>
                        </div>
                        <div className="eb-popup-content-editor">
                            {displayCloseIcon && (
                                <div className="eb-popup-close-icon">
                                    {useCloseIcon ? (
                                        <span className="dashicons dashicons-no-alt"></span>
                                    ) : (
                                        closeBtnText
                                    )}
                                </div>
                            )}
                            <InnerBlocks
                                orientation={"vertical"}
                                templateLock={false}
                                renderAppender={
                                    select("core/block-editor").getBlockOrder(
                                        clientId
                                    ).length > 0
                                        ? undefined
                                        : InnerBlocks.ButtonBlockAppender
                                }
                            />
                        </div>
                    </div>
                </div>
            </BlockProps.Edit>
        </>
    );
};

export default memo(withBlockContext(defaultAttributes)(Edit))
