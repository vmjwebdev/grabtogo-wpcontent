/**
 * Internal dependencies
 */
import { RichText } from "@wordpress/block-editor";
import {
    EBDisplayIcon,
    sanitizeURL,
    BlockProps,
    ImageComponent
} from "@essential-blocks/controls";
import { BACK_IMG_ATTR, FRONT_IMG_ATTR } from "./constants";
const Save = ({ attributes }) => {
    const {
        blockId,
        flipType,
        frontIconOrImage,
        frontImageUrl,
        frontImageAlt,
        frontIcon,
        showFrontTitle,
        frontTitle,
        showFrontContent,
        frontContent,
        backIconOrImage,
        backImageUrl,
        backImageAlt,
        backIcon,
        showBackTitle,
        backTitle,
        showBackContent,
        backContent,
        link,
        linkType,
        buttonText,
        buttonIcon,
        buttonClasses,
        contentPosition,
        linkOpenNewTab,
        flipMode,
        isMouseLeaveOn,
        classHook,
        frontTitleTag,
        backTitleTag,
    } = attributes;

    const sanitizeLink = sanitizeURL(link);

    const alignmentClass =
        contentPosition === "center"
            ? " eb-flipbox-align-center"
            : contentPosition === "right"
            ? " eb-flipbox-align-right"
            : "";
    const flipModeClass =
        flipMode === "hover" ? " eb-hover-mode" : " eb-click-mode";

    return (
        <BlockProps.Save attributes={attributes}>
            <div
                className={`eb-parent-wrapper eb-parent-${blockId} ${classHook}`}
            >
                <div
                    className={`eb-flipbox-container ${blockId}${alignmentClass}${flipModeClass}`}
                    data-id={blockId}
                    data-flip-type={flipType}
                    data-flip-mode={flipMode}
                    {...("click" === flipMode
                        ? { "data-flip-mouseleave": isMouseLeaveOn }
                        : {})}
                >
                    <div
                        className={`eb-flipper${
                            "hover" === flipMode ? " " + flipType : ""
                        }`}
                    >
                        <div className="eb-flipbox-front">
                            <div className="eb-flipbox-items-container">
                                {frontIconOrImage !== "none" && (
                                    <div className="eb-flipbox-icon-wrapper">
                                        {frontIconOrImage === "image" &&
                                            frontImageUrl && (
                                                <div className="eb-flipbox-front-image-container">
                                                    <ImageComponent.Content
                                                        attributes={attributes}
                                                        className="eb-flipbox-front-image"
                                                        imageAttrProps={FRONT_IMG_ATTR}
                                                        attrPrefix="front"
                                                    />
                                                </div>
                                            )}
                                        {frontIconOrImage === "icon" &&
                                            frontIcon && (
                                                <div
                                                    className="eb-flipbox-icon-front"
                                                    data-icon={frontIcon}
                                                >
                                                    <EBDisplayIcon
                                                        icon={frontIcon}
                                                    />
                                                </div>
                                            )}
                                    </div>
                                )}
                                {showFrontTitle && (
                                    <div className="eb-flipbox-front-title-wrapper">
                                        {linkType === "title" && link ? (
                                            <a
                                                href={link ? sanitizeLink : "#"}
                                                className="title-link"
                                            >
                                                <RichText.Content
                                                    tagName={frontTitleTag}
                                                    className="eb-flipbox-front-title"
                                                    value={frontTitle}
                                                />
                                            </a>
                                        ) : (
                                            <RichText.Content
                                                tagName={frontTitleTag}
                                                className="eb-flipbox-front-title"
                                                value={frontTitle}
                                            />
                                        )}
                                    </div>
                                )}
                                {showFrontContent && (
                                    <div className="eb-flipbox-front-content-wrapper">
                                        <RichText.Content
                                            tagName="p"
                                            className="eb-flipbox-front-content"
                                            value={frontContent}
                                        />
                                    </div>
                                )}
                            </div>
                        </div>

                        <div
                            className="eb-flipbox-back"
                            onClick={
                                linkType === "box" && link && linkOpenNewTab
                                    ? `window.open('${sanitizeLink}', '_blank');`
                                    : linkType === "box" && link
                                    ? `window.location='${sanitizeLink}'`
                                    : undefined
                            }
                        >
                            <div className="eb-flipbox-items-container">
                                {backIconOrImage !== "none" && (
                                    <div className="eb-flipbox-icon-wrapper">
                                        {backIconOrImage === "image" &&
                                            backImageUrl && (
                                                <div className="eb-flipbox-back-image-container">
                                                    <ImageComponent.Content
                                                        attributes={attributes}
                                                        className="eb-flipbox-back-image"
                                                        imageAttrProps={BACK_IMG_ATTR}
                                                        attrPrefix="back"
                                                    />
                                                </div>
                                            )}
                                        {backIconOrImage === "icon" &&
                                            backIcon && (
                                                <div
                                                    className="eb-flipbox-icon-back"
                                                    data-icon={backIcon}
                                                >
                                                    <EBDisplayIcon
                                                        icon={backIcon}
                                                    />
                                                </div>
                                            )}
                                    </div>
                                )}
                                {showBackTitle && (
                                    <div className="eb-flipbox-back-title-wrapper">
                                        {linkType === "title" && link ? (
                                            <a
                                                href={link ? sanitizeLink : "#"}
                                                className="title-link"
                                                target={
                                                    linkOpenNewTab
                                                        ? `_blank`
                                                        : `_self`
                                                }
                                                rel="noopener"
                                            >
                                                <RichText.Content
                                                    tagName={backTitleTag}
                                                    className="eb-flipbox-back-title"
                                                    value={backTitle}
                                                />
                                            </a>
                                        ) : (
                                            <RichText.Content
                                                tagName={backTitleTag}
                                                className="eb-flipbox-back-title"
                                                value={backTitle}
                                            />
                                        )}
                                    </div>
                                )}
                                {showBackContent && (
                                    <div className="eb-flipbox-back-content-wrapper">
                                        <RichText.Content
                                            tagName="p"
                                            className="eb-flipbox-back-content"
                                            value={backContent}
                                        />
                                    </div>
                                )}
                                {linkType === "button" && (
                                    <div className="eb-flipbox-button-container">
                                        <a
                                            className={`eb-flipbox-button-link ${buttonClasses}`}
                                            href={link ? sanitizeLink : "#"}
                                            target={
                                                linkOpenNewTab
                                                    ? `_blank`
                                                    : `_self`
                                            }
                                            rel="noopener"
                                        >
                                            <div className="eb-flipbox-button-content">
                                                <span>{buttonText}</span>
                                                {buttonIcon && (
                                                    <EBDisplayIcon
                                                        icon={buttonIcon}
                                                        className="eb-flipbox-button-icon"
                                                    />
                                                )}
                                            </div>
                                        </a>
                                    </div>
                                )}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </BlockProps.Save>
    );
};

export default Save;
