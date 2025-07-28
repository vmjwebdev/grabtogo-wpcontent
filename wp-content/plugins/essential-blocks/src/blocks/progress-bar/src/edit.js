/**
 * WordPress dependencies
 */
import { __ } from "@wordpress/i18n";
import { useEffect, useRef, memo } from "@wordpress/element";
import {
    BlockControls,
    AlignmentToolbar,
} from "@wordpress/block-editor";

/**
 * Internal depenencies
 */
import Inspector from "./inspector";
import Style from "./style";
import defaultAttributes from './attributes';
import {
    CONTAINER_CLASS,
    WRAPPER_CLASS,
    STRIPE_CLASS,
} from "./constants";

import {
    DynamicInputValueHandler,
    BlockProps,
    withBlockContext
} from "@essential-blocks/controls";

const Edit = (props) => {
    const {
        isSelected,
        attributes,
        setAttributes,
    } = props;
    const circle_half_left = useRef(null);
    const circle_half_right = useRef(null);
    const circle_pie = useRef(null);
    const line = useRef(null);
    const circle_half = useRef(null);
    const box = useRef(null);

    const {
        blockId,
        layout,
        wrapperAlign,
        titleTag,
        progress,
        displayProgress,
        animationDuration,
        title,
        showStripe,
        stripeAnimation,
        prefix,
        suffix,
        classHook,
        totalRange,
        valueDivider,
        valueType,
        absoluteProgress,
    } = attributes;

    useEffect(() => {
        if (totalRange === undefined) {
            setAttributes({ totalRange: 100 })
        }
        if (valueDivider === undefined) {
            setAttributes({ valueDivider: "/" })
        }
        if (valueType === undefined) {
            setAttributes({ valueType: 'percentage' })
        }
    }, []);

    useEffect(() => {
        if (layout == "line" || layout === "line_rainbow") {
            line.current.style.width = "unset";
        } else if (layout === "box") {
            box.current.style.height = "unset";
        } else if (layout === "circle" || layout === "circle_fill") {
            circle_half_left.current.style.transform = "rotate(0deg)";
            circle_pie.current.style.clipPath = "";
            circle_half_right.current.style.visibility = "";
        } else if (layout === "half_circle" || layout === "half_circle_fill") {
            circle_half.current.style.transform = "rotate(0deg)";
            circle_half.current.style.transition = "none";
        }
        let id = "";
        const changeWidthEffect = () => {
            var i = 0;
            if (i == 0) {
                i = 1;
                var width = 0;
                var value = progress;
                if (layout === "circle" || layout === "circle_fill") {
                    value = progress * 3.6;
                } else if (
                    layout === "half_circle" ||
                    layout === "half_circle_fill"
                ) {
                    value = progress * 1.8;
                }

                id = setInterval(ebChangeframe, 10);
                function ebChangeframe() {
                    if (layout === "circle" || layout === "circle_fill") {
                        if (width > 180) {
                            circle_pie.current.style.clipPath = "inset(0)";
                            circle_half_right.current.style.visibility =
                                "visible";
                        } else {
                            circle_pie.current.style.clipPath = "";
                            circle_half_right.current.style.visibility = "";
                        }
                    }
                    if (width >= value) {
                        clearInterval(id);
                        i = 0;
                    } else {
                        width++;
                        if (layout == "line" || layout === "line_rainbow") {
                            line.current.style.width = width + "%";
                        } else if (layout === "box") {
                            box.current.style.height = width + "%";
                        }
                        if (layout === "circle" || layout === "circle_fill") {
                            circle_half_left.current.style.transform =
                                "rotate(" + width + "deg)";
                        } else if (
                            layout === "half_circle" ||
                            layout === "half_circle_fill"
                        ) {
                            circle_half.current.style.transform =
                                "rotate(" + width + "deg)";
                        }
                    }
                }
            }
        };
        const progressSetTimeout = setTimeout(changeWidthEffect, 500);

        const absoluteValue = Math.round((progress / 100) * totalRange);
        setAttributes({ absoluteProgress: absoluteValue })

        return () => {
            clearInterval(id);
            clearTimeout(progressSetTimeout);
        };
    }, [layout, progress, animationDuration, totalRange]);

    useEffect(() => {
        valueType === 'percentage' ? setAttributes({ totalRange: 100 }) : null;
    }, [valueType]);

    // you must declare this variable
    const enhancedProps = {
        ...props,
        blockPrefix: 'eb-progressbar',
        style: <Style {...props} />
    };

    const stripeClass = showStripe ? " " + STRIPE_CLASS[stripeAnimation] : "";

    return (
        <>
            {isSelected && <Inspector {...props} />}
            <BlockControls>
                <AlignmentToolbar
                    value={wrapperAlign}
                    onChange={(wrapperAlign) => setAttributes({ wrapperAlign })}
                />
            </BlockControls>
            <BlockProps.Edit {...enhancedProps}>
                <div
                    className={`eb-parent-wrapper eb-parent-${blockId} ${classHook}`}
                >
                    <div className={`eb-progressbar-wrapper ${blockId}`}>
                        <div
                            className={`eb-progressbar-${CONTAINER_CLASS[layout]}-container ${wrapperAlign}`}
                        >
                            {(layout === "line" || layout === "line_rainbow") &&
                                title && (
                                    <DynamicInputValueHandler
                                        value={title}
                                        tagName={titleTag}
                                        className="eb-progressbar-title"
                                        onChange={(title) =>
                                            setAttributes({
                                                title,
                                            })
                                        }
                                        readOnly={true}
                                    />
                                )}
                            <div
                                className={`eb-progressbar ${WRAPPER_CLASS[layout]}${stripeClass}`}
                            >
                                {(layout === "circle" ||
                                    layout === "circle_fill") && (
                                        <>
                                            <div
                                                className="eb-progressbar-circle-pie"
                                                ref={circle_pie}
                                            >
                                                <div
                                                    className="eb-progressbar-circle-half-left eb-progressbar-circle-half"
                                                    ref={circle_half_left}
                                                ></div>
                                                <div
                                                    className="eb-progressbar-circle-half-right eb-progressbar-circle-half"
                                                    ref={circle_half_right}
                                                ></div>
                                            </div>
                                            <div className="eb-progressbar-circle-inner"></div>
                                            <div className="eb-progressbar-circle-inner-content">
                                                {title && (
                                                    <DynamicInputValueHandler
                                                        value={title}
                                                        tagName={titleTag}
                                                        className="eb-progressbar-title"
                                                        onChange={(title) =>
                                                            setAttributes({
                                                                title,
                                                            })
                                                        }
                                                        readOnly={true}
                                                    />
                                                )}
                                                {displayProgress && (
                                                    <span className="eb-progressbar-count-wrap">
                                                        <span className="eb-progress-count-wrap">
                                                            <span className="eb-progressbar-count">
                                                                {valueType === 'absolute' ? absoluteProgress : progress}
                                                            </span>
                                                            {valueType === 'percentage' && (
                                                                <span className="postfix">%</span>
                                                            )}
                                                        </span>
                                                        {valueType === 'absolute' && (
                                                            <>
                                                                <span className="value-divider">{valueDivider}</span>
                                                                <span className="eb-progress-total-range-wrap">
                                                                    <span className="eb-progressbar-count">
                                                                        {totalRange}
                                                                    </span>
                                                                </span>
                                                            </>
                                                        )}
                                                    </span>
                                                )}
                                            </div>
                                        </>
                                    )}

                                {(layout === "half_circle" ||
                                    layout === "half_circle_fill") && (
                                        <>
                                            <div className="eb-progressbar-circle">
                                                <div className="eb-progressbar-circle-pie">
                                                    <div
                                                        className="eb-progressbar-circle-half"
                                                        ref={circle_half}
                                                    ></div>
                                                </div>
                                                <div className="eb-progressbar-circle-inner"></div>
                                            </div>
                                            <div className="eb-progressbar-circle-inner-content">
                                                {title && (
                                                    <DynamicInputValueHandler
                                                        value={title}
                                                        tagName={titleTag}
                                                        className="eb-progressbar-title"
                                                        onChange={(title) =>
                                                            setAttributes({
                                                                title,
                                                            })
                                                        }
                                                        readOnly={true}
                                                    />
                                                )}
                                                {displayProgress && (
                                                    <span className="eb-progressbar-count-wrap">
                                                        <span className="eb-progress-count-wrap">
                                                            <span className="eb-progressbar-count">
                                                                {valueType === 'absolute' ? absoluteProgress : progress}
                                                            </span>
                                                            {valueType === 'percentage' && (
                                                                <span className="postfix">%</span>
                                                            )}
                                                        </span>
                                                        {valueType === 'absolute' && (
                                                            <>
                                                                <span className="value-divider">{valueDivider}</span>
                                                                <span className="eb-progress-total-range-wrap">
                                                                    <span className="eb-progressbar-count">
                                                                        {totalRange}
                                                                    </span>
                                                                </span>
                                                            </>
                                                        )}
                                                    </span>
                                                )}
                                            </div>
                                        </>
                                    )}

                                {(layout === "line" ||
                                    layout === "line_rainbow") && (
                                        <>
                                            {displayProgress && (
                                                <span className="eb-progressbar-count-wrap">
                                                    <span className="eb-progress-count-wrap">
                                                        <span className="eb-progressbar-count">
                                                            {valueType === 'absolute' ? absoluteProgress : progress}
                                                        </span>
                                                        {valueType === 'percentage' && (
                                                            <span className="postfix">%</span>
                                                        )}
                                                    </span>
                                                    {valueType === 'absolute' && (
                                                        <>
                                                            <span className="value-divider">{valueDivider}</span>
                                                            <span className="eb-progress-total-range-wrap">
                                                                <span className="eb-progressbar-count">
                                                                    {totalRange}
                                                                </span>
                                                            </span>
                                                        </>
                                                    )}
                                                </span>
                                            )}
                                            <span
                                                className="eb-progressbar-line-fill"
                                                ref={line}
                                            ></span>
                                        </>
                                    )}

                                {layout === "box" && (
                                    <>
                                        <div className="eb-progressbar-box-inner-content">
                                            {title && (
                                                <DynamicInputValueHandler
                                                    value={title}
                                                    tagName={titleTag}
                                                    className="eb-progressbar-title"
                                                    onChange={(title) =>
                                                        setAttributes({
                                                            title,
                                                        })
                                                    }
                                                    readOnly={true}
                                                />
                                            )}
                                            {displayProgress && (
                                                <span className="eb-progressbar-count-wrap">
                                                    <span className="eb-progress-count-wrap">
                                                        <span className="eb-progressbar-count">
                                                            {valueType === 'absolute' ? absoluteProgress : progress}
                                                        </span>
                                                        {valueType === 'percentage' && (
                                                            <span className="postfix">%</span>
                                                        )}
                                                    </span>
                                                    {valueType === 'absolute' && (
                                                        <>
                                                            <span className="value-divider">{valueDivider}</span>
                                                            <span className="eb-progress-total-range-wrap">
                                                                <span className="eb-progressbar-count">
                                                                    {totalRange}
                                                                </span>
                                                            </span>
                                                        </>
                                                    )}
                                                </span>
                                            )}
                                        </div>
                                        <div
                                            className="eb-progressbar-box-fill"
                                            ref={box}
                                        ></div>
                                    </>
                                )}
                            </div>
                            {(layout === "half_circle" ||
                                layout === "half_circle_fill") && (
                                    <>
                                        <div className="eb-progressbar-half-circle-after">
                                            <span className="eb-progressbar-prefix-label">
                                                {prefix}
                                            </span>
                                            <span className="eb-progressbar-postfix-label">
                                                {suffix}
                                            </span>
                                        </div>
                                    </>
                                )}
                        </div>
                    </div>
                </div>
            </BlockProps.Edit>
        </>
    );
}

export default memo(withBlockContext(defaultAttributes)(Edit))
