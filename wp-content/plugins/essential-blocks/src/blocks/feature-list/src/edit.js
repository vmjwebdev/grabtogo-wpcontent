/**
 * WordPress dependencies
 */
import { __ } from "@wordpress/i18n";
import { useEffect, memo } from "@wordpress/element";
/**
 * Internal dependencies
 */
import Inspector from "./inspector";
import Style from "./style";
import defaultAttributes from './attributes'

import {
    EBDisplayIcon,
    sanitizeURL,
    BlockProps,
    withBlockContext,
    useBlockSetAttributes
} from "@essential-blocks/controls";

const Edit = (props) => {
    const { attributes, isSelected, setAttributes } = props;
    const {
        blockId,
        featureListAlign,
        features,
        iconPosition,
        iconShape,
        shapeView,
        showConnector,
        connectorStyle,
        classHook,
        useInlineDesign,
    } = attributes;

    // const setAttributes = useBlockSetAttributes();

    const featureListAlignClass =
        featureListAlign === "center"
            ? " eb-feature-list-center"
            : featureListAlign === "right"
                ? " eb-feature-list-right"
                : " eb-feature-list-left";

    // you must declare this variable
    const enhancedProps = {
        ...props,
        blockPrefix: 'eb-feature-list',
        style: <Style {...props} />
    };

    const featureListWrapperClass =
        iconShape !== "none" ? ` ${iconShape} ${shapeView}` : " none";
    const inlineDesignClass = useInlineDesign ? " eb-inline-feature-list" : "";

    let iconStyle = {};

    return (
        <>
            {isSelected && (
                <Inspector attributes={attributes} setAttributes={setAttributes} />
            )}
            <BlockProps.Edit {...enhancedProps}>
                <div className={`eb-parent-wrapper eb-parent-${blockId} ${classHook}`}>
                    <div
                        className={`${blockId} eb-feature-list-wrapper eb-icon-position-${iconPosition} eb-tablet-icon-position-${iconPosition} eb-mobile-icon-position-${iconPosition}${featureListAlignClass} ${!useInlineDesign && showConnector
                            ? "connector-" + connectorStyle
                            : ""
                            }`}
                    >
                        <ul
                            className={`eb-feature-list-items ${featureListWrapperClass} ${inlineDesignClass}`}
                        >
                            {features.map(
                                (
                                    {
                                        title,
                                        iconType,
                                        featureImage,
                                        featureImageId,
                                        featureImageAlt,
                                        featureImageTitle,
                                        icon,
                                        iconColor,
                                        iconBackgroundColor,
                                        content,
                                        link,
                                        linkOpenNewTab,
                                    },
                                    index
                                ) => {
                                    {
                                        iconStyle = {
                                            color: iconColor,
                                            backgroundColor: iconBackgroundColor,
                                        };
                                    }
                                    return (
                                        <li
                                            key={index}
                                            className="eb-feature-list-item"
                                            data-new-tab={
                                                linkOpenNewTab ? linkOpenNewTab.toString() : "false"
                                            }
                                            data-icon-type={iconType}
                                            data-image={featureImage}
                                            data-image-id={featureImageId}
                                            data-alt={featureImageAlt}
                                            data-title={featureImageTitle}
                                            data-icon={icon}
                                            data-icon-color={iconColor}
                                            data-icon-background-color={iconBackgroundColor}
                                            data-link={link}
                                        >
                                            {iconType !== "none" && (
                                                <div className="eb-feature-list-icon-box">
                                                    <div className="eb-feature-list-icon-inner">
                                                        <span
                                                            className="eb-feature-list-icon"
                                                            style={iconStyle}
                                                        >
                                                            {iconType === "icon" && <EBDisplayIcon icon={icon} />}
                                                            {iconType === "image" && (
                                                                <img
                                                                    className="eb-feature-list-img"
                                                                    src={featureImage}
                                                                    alt={
                                                                        featureImageAlt
                                                                            ? featureImageAlt
                                                                            : featureImageTitle
                                                                    }
                                                                />
                                                            )}
                                                        </span>
                                                    </div>
                                                </div>
                                            )}

                                            <div className="eb-feature-list-content-box">
                                                {link ? (
                                                    <attributes.titleTag className="eb-feature-list-title">
                                                        <a href={sanitizeURL(link)}>{title}</a>
                                                    </attributes.titleTag>
                                                ) : (
                                                    <attributes.titleTag className="eb-feature-list-title">
                                                        {title}
                                                    </attributes.titleTag>
                                                )}
                                                {!useInlineDesign && (
                                                    <p className="eb-feature-list-content">{content}</p>
                                                )}
                                            </div>
                                        </li>
                                    );
                                }
                            )}
                        </ul>
                    </div>
                </div>
            </BlockProps.Edit>
        </>
    );
};

Edit.displayName = 'FeatureListEdit';

export default memo(withBlockContext(defaultAttributes)(Edit))
