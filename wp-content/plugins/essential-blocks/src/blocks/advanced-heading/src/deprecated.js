/**
 * WordPress dependencies
 */
import { RichText, useBlockProps } from "@wordpress/block-editor";
import { EBDisplayIcon, BlockProps } from "@essential-blocks/controls";

import attributes from "./attributes";
const { omit } = lodash;
const deprecated = [
    {
        attributes: { ...attributes },
        supports: {
            anchor: true,
        },
        save: ({ attributes }) => {
            const {
                blockId,
                preset,
                tagName: TagName,
                titleText,
                subtitleTagName,
                displaySubtitle,
                subtitleText,
                seperatorType,
                displaySeperator,
                seperatorPosition,
                separatorIcon,
                classHook,
                source,
                enableLink,
                titleLink,
                openInNewTab,
            } = attributes;

            if (source == "dynamic-title") return null;
            const linkTarget = openInNewTab ? "_blank" : undefined;

            return (
                <BlockProps.Save attributes={attributes}>
                    <div
                        className={`eb-parent-wrapper eb-parent-${blockId} ${classHook}`}
                    >
                        <div
                            className={`eb-advance-heading-wrapper ${blockId} ${preset}`}
                            data-id={blockId}
                        >
                            {displaySeperator &&
                                seperatorPosition === "top" && (
                                    <div
                                        className={
                                            "eb-ah-separator " + seperatorType
                                        }
                                    >
                                        {seperatorType === "icon" && (
                                            <EBDisplayIcon
                                                icon={separatorIcon}
                                            />
                                        )}
                                    </div>
                                )}
                            {enableLink && titleLink.length > 0 && (
                                <TagName className="eb-ah-title">
                                    <a
                                        href={titleLink}
                                        target={linkTarget}
                                        rel={
                                            linkTarget === "_blank"
                                                ? "noopener"
                                                : undefined
                                        }
                                    >
                                        {titleText}
                                    </a>
                                </TagName>
                            )}

                            {(!enableLink ||
                                (enableLink && titleLink.length == 0)) && (
                                <RichText.Content
                                    tagName={TagName}
                                    className="eb-ah-title"
                                    value={titleText}
                                />
                            )}

                            {displaySubtitle && (
                                <RichText.Content
                                    tagName={subtitleTagName}
                                    className="eb-ah-subtitle"
                                    value={subtitleText}
                                />
                            )}
                            {displaySeperator &&
                                seperatorPosition === "bottom" && (
                                    <div
                                        className={
                                            "eb-ah-separator " + seperatorType
                                        }
                                    >
                                        {seperatorType === "icon" && (
                                            <EBDisplayIcon
                                                icon={separatorIcon}
                                            />
                                        )}
                                    </div>
                                )}
                        </div>
                    </div>
                </BlockProps.Save>
            );
        },
    },
    {
        attributes: {
            ...omit({ ...attributes }, [
                "source",
                "enableLink",
                "titleLink",
                "openInNewTab",
            ]),
        },
        supports: {
            anchor: true,
        },
        save: ({ attributes }) => {
            const {
                blockId,
                preset,
                tagName,
                titleText,
                subtitleTagName,
                displaySubtitle,
                subtitleText,
                seperatorType,
                displaySeperator,
                seperatorPosition,
                separatorIcon,
                classHook,
            } = attributes;

            return (
                <div {...useBlockProps.save()}>
                    <div
                        className={`eb-parent-wrapper eb-parent-${blockId} ${classHook}`}
                    >
                        <div
                            className={`eb-advance-heading-wrapper ${blockId} ${preset}`}
                            data-id={blockId}
                        >
                            {displaySeperator &&
                                seperatorPosition === "top" && (
                                    <div
                                        className={
                                            "eb-ah-separator " + seperatorType
                                        }
                                    >
                                        {seperatorType === "icon" && (
                                            // <i
                                            //     className={`${separatorIcon
                                            //         ? separatorIcon
                                            //         : "fas fa-arrow-circle-down"
                                            //         }`}
                                            // ></i>
                                            <EBDisplayIcon
                                                icon={separatorIcon}
                                            />
                                        )}
                                    </div>
                                )}
                            <RichText.Content
                                tagName={tagName}
                                className="eb-ah-title"
                                value={titleText}
                            />
                            {displaySubtitle && (
                                <RichText.Content
                                    tagName={subtitleTagName}
                                    className="eb-ah-subtitle"
                                    value={subtitleText}
                                />
                            )}
                            {displaySeperator &&
                                seperatorPosition === "bottom" && (
                                    <div
                                        className={
                                            "eb-ah-separator " + seperatorType
                                        }
                                    >
                                        {seperatorType === "icon" && (
                                            // <i
                                            //     className={`${separatorIcon
                                            //         ? separatorIcon
                                            //         : "fas fa-arrow-circle-down"
                                            //         }`}
                                            // ></i>
                                            <EBDisplayIcon
                                                icon={separatorIcon}
                                            />
                                        )}
                                    </div>
                                )}
                        </div>
                    </div>
                </div>
            );
        },
    },
    {
        attributes: {
            ...attributes,
            titleText: {
                type: "string",
                default: "Essential Block Advanced Heading",
            },
            subtitleText: {
                type: "string",
                default: "Essential Block Advance Subtitle",
            },
        },
        supports: {
            anchor: true,
        },
        save: ({ attributes }) => {
            const {
                blockId,
                preset,
                tagName,
                titleText,
                subtitleTagName,
                displaySubtitle,
                subtitleText,
                seperatorType,
                displaySeperator,
                seperatorPosition,
                separatorIcon,
                classHook,
            } = attributes;

            return (
                <div {...useBlockProps.save()}>
                    <div
                        className={`eb-parent-wrapper eb-parent-${blockId} ${classHook}`}
                    >
                        <div
                            className={`eb-advance-heading-wrapper ${blockId} ${preset}`}
                            data-id={blockId}
                        >
                            {displaySeperator &&
                                seperatorPosition === "top" && (
                                    <div
                                        className={
                                            "eb-ah-separator " + seperatorType
                                        }
                                    >
                                        {seperatorType === "icon" && (
                                            <i
                                                className={`${
                                                    separatorIcon
                                                        ? separatorIcon
                                                        : "fas fa-arrow-circle-down"
                                                }`}
                                            ></i>
                                        )}
                                    </div>
                                )}
                            <RichText.Content
                                tagName={tagName}
                                className="eb-ah-title"
                                value={titleText}
                            />
                            {displaySubtitle && (
                                <RichText.Content
                                    tagName={subtitleTagName}
                                    className="eb-ah-subtitle"
                                    value={subtitleText}
                                />
                            )}
                            {displaySeperator &&
                                seperatorPosition === "bottom" && (
                                    <div
                                        className={
                                            "eb-ah-separator " + seperatorType
                                        }
                                    >
                                        {seperatorType === "icon" && (
                                            <i
                                                className={`${
                                                    separatorIcon
                                                        ? separatorIcon
                                                        : "fas fa-arrow-circle-down"
                                                }`}
                                            ></i>
                                        )}
                                    </div>
                                )}
                        </div>
                    </div>
                </div>
            );
        },
    },
    {
        attributes: { ...attributes },
        supports: {
            anchor: true,
        },
        save: ({ attributes }) => {
            const {
                blockId,
                preset,
                tagName,
                titleText,
                subtitleTagName,
                displaySubtitle,
                subtitleText,
                seperatorType,
                displaySeperator,
                separatorIcon,
                classHook,
            } = attributes;

            return (
                <div {...useBlockProps.save()}>
                    <div
                        className={`eb-parent-wrapper eb-parent-${blockId} ${classHook}`}
                    >
                        <div
                            className={`eb-advance-heading-wrapper ${blockId} ${preset}`}
                            data-id={blockId}
                        >
                            <RichText.Content
                                tagName={tagName}
                                className="eb-ah-title"
                                value={titleText}
                            />
                            {displaySubtitle && (
                                <RichText.Content
                                    tagName={subtitleTagName}
                                    className="eb-ah-subtitle"
                                    value={subtitleText}
                                />
                            )}
                            {displaySeperator && (
                                <div
                                    className={
                                        "eb-ah-separator " + seperatorType
                                    }
                                >
                                    {seperatorType === "icon" && (
                                        <i
                                            className={`${
                                                separatorIcon
                                                    ? separatorIcon
                                                    : "fas fa-arrow-circle-down"
                                            }`}
                                        ></i>
                                    )}
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            );
        },
    },
    {
        attributes: { ...attributes },
        supports: {
            anchor: true,
        },
        save: ({ attributes }) => {
            const {
                blockId,
                preset,
                tagName,
                titleText,
                displaySubtitle,
                subtitleText,
                seperatorType,
                displaySeperator,
                separatorIcon,
            } = attributes;

            return (
                <div {...useBlockProps.save()}>
                    <div
                        className={`eb-advance-heading-wrapper ${blockId} ${preset}`}
                        data-id={blockId}
                    >
                        <RichText.Content
                            tagName={tagName}
                            className="eb-ah-title"
                            value={titleText}
                        />
                        {displaySubtitle && (
                            <RichText.Content
                                tagName={"p"}
                                className="eb-ah-subtitle"
                                value={subtitleText}
                            />
                        )}
                        {displaySeperator && (
                            <div className={"eb-ah-separator " + seperatorType}>
                                {seperatorType === "icon" && (
                                    <i
                                        className={`${
                                            separatorIcon
                                                ? separatorIcon
                                                : "fas fa-arrow-circle-down"
                                        }`}
                                    ></i>
                                )}
                            </div>
                        )}
                    </div>
                </div>
            );
        },
    },
    {
        attributes: { ...attributes },
        save: ({ attributes }) => {
            const {
                blockId,
                preset,
                tagName,
                titleText,
                displaySubtitle,
                subtitleText,
                seperatorType,
                displaySeperator,
                separatorIcon,
            } = attributes;

            return (
                <div {...useBlockProps.save()}>
                    <div
                        className={`eb-advance-heading-wrapper ${blockId} ${preset}`}
                        data-id={blockId}
                    >
                        <RichText.Content
                            tagName={tagName}
                            className="eb-ah-title"
                            value={titleText}
                        />
                        {displaySubtitle && (
                            <RichText.Content
                                tagName={"p"}
                                className="eb-ah-subtitle"
                                value={subtitleText}
                            />
                        )}
                        {displaySeperator && (
                            <div className={"eb-ah-separator " + seperatorType}>
                                {seperatorType === "icon" && (
                                    <i
                                        className={`${
                                            separatorIcon
                                                ? separatorIcon
                                                : "fas fa-arrow-circle-down"
                                        }`}
                                    ></i>
                                )}
                            </div>
                        )}
                    </div>
                </div>
            );
        },
    },
];

export default deprecated;
