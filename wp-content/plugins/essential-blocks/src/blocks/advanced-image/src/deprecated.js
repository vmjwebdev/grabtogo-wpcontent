/**
 * WordPress dependencies
 */
import { RichText, useBlockProps } from "@wordpress/block-editor";
import { sanitizeURL, BlockProps } from "@essential-blocks/controls";
import attributes from "./attributes";
const { omit } = lodash;

const deprecated = [
    {
        attributes: { ...attributes },
        migrate(attributes) {
            const { image } = attributes;
            const newAttributes = { ...attributes };

            return {
                ...newAttributes,
                imageUrl: image?.url,
                imageId: image?.id,
                imageAlt: image?.alt,
            };
        },
        supports: {
            anchor: true,
            color: {
                __experimentalDuotone: "img",
                text: false,
                background: false,
            },
        },
        save: ({ attributes }) => {
            const {
                blockId,
                image,
                imageCaption,
                horizontalAlign,
                verticalAlign,
                verticalAlignCap2,
                stylePreset,
                captionStyle,
                hoverEffect,
                openInNewTab,
                imageLink,
                enableLink,
                classHook,
                imgSource,
            } = attributes;

            if (imgSource !== "custom") {
                return null;
            }

            let imageURL = image.url;

            const linkTarget = openInNewTab ? "_blank" : undefined;

            if (imageURL === "") return null;

            return (
                <BlockProps.Save attributes={attributes}>
                    <div
                        className={`eb-parent-wrapper eb-parent-${blockId} ${classHook}`}
                    >
                        <figure
                            className={`eb-advanced-image-wrapper ${blockId} img-style-${stylePreset} ${captionStyle} caption-horizontal-${horizontalAlign} caption-vertical-${verticalAlign} ${verticalAlignCap2} ${hoverEffect}`}
                            data-id={blockId}
                        >
                            <div className="image-wrapper">
                                {enableLink && (
                                    <a
                                        className={"eb-advimg-link"}
                                        href={sanitizeURL(imageLink)}
                                        target={linkTarget}
                                        rel={
                                            linkTarget === "_blank"
                                                ? "noopener"
                                                : undefined
                                        }
                                    ></a>
                                )}
                                <img src={imageURL} alt={image.alt} />
                                {!RichText.isEmpty(imageCaption) &&
                                    captionStyle != "caption-style-2" && (
                                        <RichText.Content
                                            tagName="figcaption"
                                            value={imageCaption}
                                        />
                                    )}
                            </div>

                            {!RichText.isEmpty(imageCaption) &&
                                captionStyle == "caption-style-2" && (
                                    <RichText.Content
                                        tagName="figcaption"
                                        value={imageCaption}
                                    />
                                )}
                        </figure>
                    </div>
                </BlockProps.Save>
            );
        },
    },
    {
        attributes: { ...attributes },
        supports: {
            anchor: true,
            color: {
                __experimentalDuotone: "img",
                text: false,
                background: false,
            },
        },
        save: ({ attributes }) => {
            const {
                blockId,
                image,
                imageCaption,
                horizontalAlign,
                verticalAlign,
                verticalAlignCap2,
                stylePreset,
                captionStyle,
                hoverEffect,
                openInNewTab,
                imageLink,
                enableLink,
                classHook,
                imgSource,
            } = attributes;
            if (imgSource !== "custom") {
                return null;
            }

            let imageURL = image.url;

            const linkTarget = openInNewTab ? "_blank" : undefined;

            if (imageURL === "") return null;

            return (
                <div {...useBlockProps.save()}>
                    <div
                        className={`eb-parent-wrapper eb-parent-${blockId} ${classHook}`}
                    >
                        <figure
                            className={`eb-advanced-image-wrapper ${blockId} img-style-${stylePreset} ${captionStyle} caption-horizontal-${horizontalAlign} caption-vertical-${verticalAlign} ${verticalAlignCap2} ${hoverEffect}`}
                            data-id={blockId}
                        >
                            <div className="image-wrapper">
                                {enableLink && (
                                    <a
                                        className={"eb-advimg-link"}
                                        href={imageLink}
                                        target={linkTarget}
                                        rel={
                                            linkTarget === "_blank"
                                                ? "noopener"
                                                : undefined
                                        }
                                    ></a>
                                )}
                                <img src={imageURL} alt={image.alt} />
                                {!RichText.isEmpty(imageCaption) &&
                                    captionStyle != "caption-style-2" && (
                                        <RichText.Content
                                            tagName="figcaption"
                                            value={imageCaption}
                                        />
                                    )}
                            </div>

                            {!RichText.isEmpty(imageCaption) &&
                                captionStyle == "caption-style-2" && (
                                    <RichText.Content
                                        tagName="figcaption"
                                        value={imageCaption}
                                    />
                                )}
                        </figure>
                    </div>
                </div>
            );
        },
    },
    {
        attributes: { ...omit({ ...attributes }, ["imgSource"]) },
        supports: {
            anchor: true,
            color: {
                __experimentalDuotone: "img",
                text: false,
                background: false,
            },
        },
        save: ({ attributes }) => {
            const {
                blockId,
                image,
                imageCaption,
                horizontalAlign,
                verticalAlign,
                verticalAlignCap2,
                stylePreset,
                captionStyle,
                hoverEffect,
                openInNewTab,
                imageLink,
                enableLink,
                classHook,
            } = attributes;

            let imageURL = image.url;

            const linkTarget = openInNewTab ? "_blank" : undefined;

            if (imageURL === "") return null;

            return (
                <div {...useBlockProps.save()}>
                    <div
                        className={`eb-parent-wrapper eb-parent-${blockId} ${classHook}`}
                    >
                        <figure
                            className={`eb-advanced-image-wrapper ${blockId} img-style-${stylePreset} ${captionStyle} caption-horizontal-${horizontalAlign} caption-vertical-${verticalAlign} ${verticalAlignCap2} ${hoverEffect}`}
                            data-id={blockId}
                        >
                            <div className="image-wrapper">
                                {enableLink && (
                                    <a
                                        className={"eb-advimg-link"}
                                        href={sanitizeURL(imageLink)}
                                        target={linkTarget}
                                        rel={
                                            linkTarget === "_blank"
                                                ? "noopener"
                                                : undefined
                                        }
                                    ></a>
                                )}
                                <img src={imageURL} alt={image.alt} />
                                {!RichText.isEmpty(imageCaption) &&
                                    captionStyle != "caption-style-2" && (
                                        <RichText.Content
                                            tagName="figcaption"
                                            value={imageCaption}
                                        />
                                    )}
                            </div>

                            {!RichText.isEmpty(imageCaption) &&
                                captionStyle == "caption-style-2" && (
                                    <RichText.Content
                                        tagName="figcaption"
                                        value={imageCaption}
                                    />
                                )}
                        </figure>
                    </div>
                </div>
            );
        },
    },
    {
        attributes: { ...attributes },
        supports: {
            anchor: true,
            color: {
                __experimentalDuotone: "img",
                text: false,
                background: false,
            },
        },
        save: ({ attributes }) => {
            const {
                blockId,
                image,
                imageCaption,
                horizontalAlign,
                verticalAlign,
                verticalAlignCap2,
                stylePreset,
                captionStyle,
                hoverEffect,
                openInNewTab,
                imageLink,
                enableLink,
            } = attributes;

            let imageURL = image.url;

            const linkTarget = openInNewTab ? "_blank" : undefined;

            if (imageURL === "") return null;

            return (
                <div {...useBlockProps.save()}>
                    <figure
                        className={`eb-advanced-image-wrapper ${blockId} img-style-${stylePreset} ${captionStyle} caption-horizontal-${horizontalAlign} caption-vertical-${verticalAlign} ${verticalAlignCap2} ${hoverEffect}`}
                        data-id={blockId}
                    >
                        <div className="image-wrapper">
                            {enableLink && (
                                <a
                                    className={"eb-advimg-link"}
                                    href={sanitizeURL(imageLink)}
                                    target={linkTarget}
                                    rel={
                                        linkTarget === "_blank"
                                            ? "noopener"
                                            : undefined
                                    }
                                ></a>
                            )}
                            <img src={imageURL} alt={image.alt} />
                            {!RichText.isEmpty(imageCaption) &&
                                captionStyle != "caption-style-2" && (
                                    <RichText.Content
                                        tagName="figcaption"
                                        value={imageCaption}
                                    />
                                )}
                        </div>

                        {!RichText.isEmpty(imageCaption) &&
                            captionStyle == "caption-style-2" && (
                                <RichText.Content
                                    tagName="figcaption"
                                    value={imageCaption}
                                />
                            )}
                    </figure>
                </div>
            );
        },
    },
];

export default deprecated;
