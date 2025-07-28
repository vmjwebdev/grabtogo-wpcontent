/**
 * WordPress Dependencies
 */
import { __ } from "@wordpress/i18n";
import { useRef, memo } from "@wordpress/element";
import { MediaUpload } from "@wordpress/block-editor";
import { Button } from "@wordpress/components";
import { dispatch } from "@wordpress/data";

/**
 * Internal Import
 */
import ReactCompareImage from "react-compare-image";
import Inspector from "./inspector";
import Style from "./style";
import defaultAttributes from "./attributes";

import {
    BlockProps,
    withBlockContext,
    EBMediaPlaceholder,
} from "@essential-blocks/controls";

function Edit(props) {
    const { attributes, setAttributes, className, clientId, isSelected, name } =
        props;
    const {
        blockId,
        blockMeta,
        // responsive control attribute ⬇
        resOption,
        leftImageURL,
        rightImageURL,
        hover,
        verticalMode,
        showLabels,
        beforeLabel,
        afterLabel,
        position,
        lineWidth,
        lineColor,
        contentPosition,
        horizontalLabelPosition,
        verticalLabelPosition,
        noHandle,
        classHook,
    } = attributes;

    const hiddenImg = useRef(null);

    // you must declare this variable
    const enhancedProps = {
        ...props,
        blockPrefix: "eb-image-comparison",
        style: <Style {...props} />,
    };

    let labelPostionClass = verticalMode
        ? ` eb-label-vertical-${verticalLabelPosition}`
        : ` eb-label-horizontal-${horizontalLabelPosition}`;

    const hasBothImages = leftImageURL && rightImageURL;
    const alignmentClass =
        contentPosition === "center"
            ? " eb-image-comparison-align-center"
            : contentPosition === "right"
            ? " eb-image-comparison-align-right"
            : "";
    const onImageSwap = () => {
        let { leftImageURL, rightImageURL, swap } = attributes;
        swap = !swap;
        [leftImageURL, rightImageURL] = [rightImageURL, leftImageURL];

        setAttributes({ swap, leftImageURL, rightImageURL });
    };

    if (hiddenImg.current) {
        hiddenImg.current.addEventListener("click", function () {
            dispatch("core/block-editor").selectBlock(clientId);
            dispatch("core/edit-post").openGeneralSidebar("edit-post/block");
        });
    }

    return (
        <>
            {isSelected && (
                <Inspector
                    key="inspector"
                    attributes={attributes}
                    setAttributes={setAttributes}
                    onImageSwap={onImageSwap}
                />
            )}
            <BlockProps.Edit {...enhancedProps}>
                <div
                    className={`eb-parent-wrapper eb-parent-${blockId} ${classHook}`}
                >
                    <div
                        className={`eb-image-comparison-wrapper ${blockId}${alignmentClass}${labelPostionClass}`}
                    >
                        {hasBothImages ? (
                            <>
                                <div
                                    className="eb-image-comparison-hide"
                                    ref={hiddenImg}
                                >
                                    <ReactCompareImage
                                        leftImage={leftImageURL}
                                        rightImage={rightImageURL}
                                        {...(verticalMode
                                            ? { vertical: "vertical" }
                                            : {})}
                                        {...(hover ? { hover: "hover" } : {})}
                                        {...(showLabels
                                            ? { leftImageLabel: beforeLabel }
                                            : {})}
                                        {...(showLabels
                                            ? { rightImageLabel: afterLabel }
                                            : {})}
                                        {...(noHandle
                                            ? { handle: <React.Fragment /> }
                                            : {})}
                                        sliderPositionPercentage={
                                            position / 100
                                        }
                                        sliderLineWidth={
                                            lineWidth ? lineWidth : 0
                                        }
                                        sliderLineColor={lineColor}
                                    />
                                </div>
                            </>
                        ) : (
                            <div className="eb-image-comparison-placeholder">
                                {!leftImageURL && (
                                    <EBMediaPlaceholder
                                        onSelect={(media) =>
                                            setAttributes({
                                                leftImageURL: media.url,
                                            })
                                        }
                                        allowTypes={["image"]}
                                        labels={{
                                            title: __(
                                                "Left Image",
                                                "essential-blocks",
                                            ),
                                            instructions:
                                                "Drag media file, upload or select files from your library.",
                                        }}
                                    />
                                )}
                                {leftImageURL && (
                                    <img
                                        className="eb-image-comparison-image"
                                        src={leftImageURL}
                                    />
                                )}
                                {!rightImageURL && (
                                    <EBMediaPlaceholder
                                        onSelect={(media) =>
                                            setAttributes({
                                                rightImageURL: media.url,
                                            })
                                        }
                                        allowTypes={["image"]}
                                        labels={{
                                            title: __(
                                                "Right Image",
                                                "essential-blocks",
                                            ),
                                            instructions:
                                                "Drag media file, upload or select files from your library.",
                                        }}
                                    />
                                )}
                                {rightImageURL && (
                                    <img
                                        className="eb-image-comparison-image"
                                        src={rightImageURL}
                                    />
                                )}
                            </div>
                        )}
                    </div>
                </div>
            </BlockProps.Edit>
        </>
    );
}
export default memo(withBlockContext(defaultAttributes)(Edit));
