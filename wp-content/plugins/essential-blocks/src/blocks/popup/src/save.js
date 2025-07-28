/**
 * WordPress dependencies
 */
import { RichText, InnerBlocks } from "@wordpress/block-editor";
import { EBDisplayIcon, BlockProps } from "@essential-blocks/controls";
const Save = ({ attributes }) => {
    const {
        blockId,
        classHook,
        trigger,
        btnText,
        btnIcon,
        iconPosition,
        btnAlignment,
        displayCloseIcon,
        escToExit,
        clickToExit,
        position,
        btnType,
        triggerIcon,
        pageLoadDelay,
        eleIdentifier,
        useCloseIcon,
        closeBtnText,
        autoExit,
        autoExitTime,
        useCookies,
        cookieExpireTime,
        disablePageScroll,
        scrollType,
        scrollPercentage,
        scrollElement,
        scrollDistance,
        scrollOffset,
    } = attributes;
    const alignmentClass =
        "left" === btnAlignment
            ? " alignment-left"
            : "right" === btnAlignment
            ? " alignment-right"
            : " alignment-center";
    return (
        <BlockProps.Save attributes={attributes}>
            <div className="eb-parent-wrapper">
                <div
                    id={blockId}
                    className={`eb-popup-container ${blockId}`}
                    data-block-id={blockId}
                    data-popup-type={trigger}
                    data-popup-delay={
                        "page_load" == trigger ? pageLoadDelay : undefined
                    }
                    data-external-identifier={
                        "external" == trigger ? eleIdentifier : undefined
                    }
                    data-close-btn={displayCloseIcon}
                    data-esc-btn={escToExit}
                    data-click-exit={clickToExit}
                    data-auto-exit={autoExit}
                    data-auto-exit-time={autoExitTime}
                    data-use-cookie={useCookies}
                    data-cookie-expire-time={cookieExpireTime}
                    data-page-scroll={disablePageScroll}
                    data-scroll-type={scrollType}
                    data-scroll-percentage={scrollPercentage}
                    data-scroll-element={scrollElement}
                    data-scroll-distance={scrollDistance}
                    data-scroll-offset={scrollOffset}
                >
                    <div
                        className={`eb-popup-btn-wrapper eb-parent-${blockId} ${classHook}`}
                    >
                        {"btn_click" === trigger && (
                            <>
                                <div
                                    className={`eb-popup-button${alignmentClass}`}
                                >
                                    <a
                                        className="eb-popup-button-anchor"
                                        role="button"
                                    >
                                        {"button" === btnType && (
                                            <>
                                                {btnIcon &&
                                                    "left" === iconPosition && (
                                                        <>
                                                            <EBDisplayIcon
                                                                classNam={`eb-popup-button-icon eb-popup-button-icon-left`}
                                                                icon={btnIcon}
                                                            />
                                                        </>
                                                    )}
                                                <RichText.Content
                                                    value={btnText}
                                                    tagName="p"
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
                                                <EBDisplayIcon
                                                    classNam={`eb-popup-icon`}
                                                    icon={triggerIcon}
                                                />
                                            </>
                                        )}
                                    </a>
                                </div>
                            </>
                        )}
                    </div>
                    <div className="eb-popup-overlay"></div>
                    <div className="modal-main-wrap">
                        <div
                            className={`eb-modal-container eb_popup_${position}`}
                        >
                            <div className="eb-popup-content">
                                {displayCloseIcon && (
                                    <>
                                        <div className="eb-popup-close-icon">
                                            {useCloseIcon ? (
                                                <span className="dashicons dashicons-no-alt"></span>
                                            ) : (
                                                closeBtnText
                                            )}
                                        </div>
                                    </>
                                )}
                                <InnerBlocks.Content />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </BlockProps.Save>
    );
};

export default Save;
