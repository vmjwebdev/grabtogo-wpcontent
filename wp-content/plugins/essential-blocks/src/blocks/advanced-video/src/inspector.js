/**
 * WordPress dependencies
 */
import { __ } from "@wordpress/i18n";
import {
    PanelBody,
    SelectControl,
    ToggleControl,
    Button,
    ButtonGroup,
    BaseControl,
    TextControl,
    PanelRow,
    __experimentalDivider as Divider,
} from "@wordpress/components";
import { useEffect, useState } from "@wordpress/element";

/**
 * Internal depencencies
 */
import {
    WRAPPER_BG,
    WRAPPER_MARGIN,
    WRAPPER_PADDING,
    WRAPPER_BORDER_SHADOW,
    VIDEO_WIDTH,
    SIZE_UNIT_TYPES,
    VIDEO_BORDER_SHADOW,
    OPTIONS,
    PLAY_ICON_WIDTH,
    LIGHTBOX_WIDTH,
    LIGHTBOX_HEIGHT,
    LIGHTBOX_BORDER_SHADOW,
    CLOSE_ICON_WIDTH,
    STICKY_VIDEO_HEIGHT,
    STICKY_VIDEO_WIDTH,
    PLACEHOLDER_IMAGE_WIDTH,
    PLACEHOLDER_IMAGE_HEIGHT,
    PLACEHOLDER_PLAY_ICON_WIDTH,
    LIGHTBOX_UNIT_TYPES,
    STICKY_POSITION,
    ALIGNMENT,
    ICON_TYPE,
    stickyVisibility,
    STICKY_VISIBILITY,
} from "./constants";

import {
    BorderShadowControl,
    ImageComponent,
    ResponsiveRangeController,
    ColorControl,
    ResponsiveSelectController,
    EBIconPicker,
    InspectorPanel, EBDisplayIcon,
} from '@essential-blocks/controls'

function Inspector(props) {
    const { attributes, setAttributes, preview, setPreview, setVideoPlayIcon } = props;
    const {
        resOption,
        showBar,
        videoConfig,
        videoOptions,
        imageOverlay,
        videoURL,
        previewImage,
        previewImageId,
        customPlayIcon,
        lightboxPlayIcon,
        customPlayIconId,
        customPlayIconURL,
        lightboxBGColor,
        closeIconColor,
        closeIconBgColor,
        placeholderImage,
        placeholderImageId,
        placeholderPlayIconURL,
        placeholderPlayIconId,
        stickyPosition,
        videoAlignment,
        customPlayIconlib,
        placeholderCustomPlayIconType,
        customPlayIconlibColor,
        lightboxPlayIconType,
        lightboxPlayIconlib,
        lightboxPlayIconlibColor,
        showDownload,
    } = attributes;

    useEffect(() => {
        if (videoOptions === "lightbox") {
            setAttributes({
                imageOverlay: false,
            });
        }
    }, [videoOptions]);

    useEffect(() => {
        if (imageOverlay && previewImage) {
            setPreview(previewImage);
        } else {
            setPreview(false);
        }
    }, [imageOverlay, previewImage]);

    useEffect(() => {
        if (customPlayIcon) {
            if (placeholderCustomPlayIconType === "image") {
                setVideoPlayIcon(<img src={customPlayIconURL} />);
            } else {
                setVideoPlayIcon(<EBDisplayIcon icon={customPlayIconlib} />);
            }
        } else {
            setVideoPlayIcon(null);
        }
    }, [customPlayIcon, customPlayIconURL, placeholderCustomPlayIconType, customPlayIconlib]);

    const [selfhostVideo, setSelfhostVideo] = useState(false);

    useEffect(() => {
        if (videoURL) {
            const extension = videoURL.split(".").pop();
            const fileFormats = ["mp4", "webm", "ogg"];

            if (fileFormats.indexOf(extension) === -1) {
                setSelfhostVideo(false);
            } else {
                setSelfhostVideo(true);
            }
        }
    }, [videoURL]);

    return (
        <InspectorPanel advancedControlProps={{
            marginPrefix: WRAPPER_MARGIN,
            paddingPrefix: WRAPPER_PADDING,
            backgroundPrefix: WRAPPER_BG,
            borderPrefix: WRAPPER_BORDER_SHADOW,
            hasMargin: true
        }}>
            <InspectorPanel.General>
                <>
                    <PanelBody title={__("General", "essential-blocks")} initialOpen={true}>
                        <TextControl
                            label={__("URL", "essential-blocks")}
                            value={videoURL}
                            onChange={(videoURL) => setAttributes({ videoURL })}
                        />
                        <Divider />
                        <PanelRow> {__("Video Options", "essential-blocks")} </PanelRow>

                        {videoOptions != "lightbox" && (
                            <>
                                <ToggleControl
                                    label={__("Autoplay", "essential-blocks")}
                                    checked={videoConfig.autoplay}
                                    onChange={(autoplay) => {
                                        setAttributes({
                                            videoConfig: {
                                                ...videoConfig,
                                                autoplay: autoplay,
                                                muted: autoplay && !preview
                                            },
                                        })
                                    }}
                                />
                                {videoConfig.autoplay && (
                                    <PanelRow>
                                        <em>Audio will be muted by default if autoplay is enabled.</em>
                                    </PanelRow>
                                )}

                                <ToggleControl
                                    label={__("Mute", "essential-blocks")}
                                    checked={videoConfig.muted}
                                    onChange={(muted) =>
                                        setAttributes({
                                            videoConfig: {
                                                ...videoConfig,
                                                muted: muted,
                                            },
                                        })
                                    }
                                />
                            </>
                        )}

                        <ToggleControl
                            label={__("Loop", "essential-blocks")}
                            checked={videoConfig.loop}
                            onChange={(loop) =>
                                setAttributes({
                                    videoConfig: {
                                        ...videoConfig,
                                        loop: loop,
                                    },
                                })
                            }
                        />
                        <ToggleControl
                            label={__("Show Controls", "essential-blocks")}
                            checked={showBar}
                            onChange={(showBar) => {
                                const url = videoURL;
                                setAttributes({
                                    showBar,
                                    videoURL: ''
                                })
                                setTimeout(() => {
                                    setAttributes({
                                        videoURL: url
                                    });
                                }, 100);

                            }}
                        />

                        {showBar && selfhostVideo && (
                            <ToggleControl
                                label={__("Show Download", "essential-blocks")}
                                checked={showDownload}
                                onChange={(showDownload) => setAttributes({ showDownload })}
                            />
                        )}

                        {videoOptions != "lightbox" && (
                            <>
                                <Divider />

                                <ToggleControl
                                    label={__("Image Overlay", "essential-blocks")}
                                    checked={imageOverlay}
                                    onChange={(imageOverlay) =>
                                        setAttributes({
                                            imageOverlay,
                                        })
                                    }
                                />

                                {imageOverlay && (
                                    <>
                                        <ImageComponent.GeneralTab
                                            onSelect={({ id, url }) => setAttributes({ previewImage: url, previewImageId: id })}
                                            value={previewImage}
                                            hasTag={false}
                                            hasCaption={false}
                                            hasStyle={false}
                                            hasLink={false}
                                            showInPanel={false}
                                        />

                                        <ToggleControl
                                            label={__("Custom Play Icon", "essential-blocks")}
                                            checked={customPlayIcon}
                                            onChange={(customPlayIcon) =>
                                                setAttributes({
                                                    customPlayIcon,
                                                })
                                            }
                                        />

                                        {customPlayIcon && (
                                            <>
                                                <BaseControl
                                                    label={__("Icon Type", "essential-blocks")}
                                                >
                                                    <ButtonGroup>
                                                        {ICON_TYPE.map((item) => (
                                                            <Button
                                                                // isLarge
                                                                isPrimary={
                                                                    placeholderCustomPlayIconType ===
                                                                    item.value
                                                                }
                                                                isSecondary={
                                                                    placeholderCustomPlayIconType !==
                                                                    item.value
                                                                }
                                                                onClick={() =>
                                                                    setAttributes({
                                                                        placeholderCustomPlayIconType:
                                                                            item.value,
                                                                    })
                                                                }
                                                            >
                                                                {item.label}
                                                            </Button>
                                                        ))}
                                                    </ButtonGroup>
                                                </BaseControl>

                                                {placeholderCustomPlayIconType === "icon" && (
                                                    <BaseControl>
                                                        <EBIconPicker
                                                            value={customPlayIconlib}
                                                            attributeName={'customPlayIconlib'}
                                                        />
                                                    </BaseControl>
                                                )}
                                                {placeholderCustomPlayIconType === "image" && (
                                                    <ImageComponent.GeneralTab
                                                        onSelect={({ id, url }) => setAttributes({
                                                            customPlayIconURL: url, customPlayIconId: id
                                                        })}
                                                        value={customPlayIconURL}
                                                        hasTag={false}
                                                        hasCaption={false}
                                                        hasStyle={false}
                                                        hasLink={false}
                                                        showInPanel={false}
                                                    />
                                                )}
                                            </>
                                        )}
                                    </>
                                )}
                            </>
                        )}

                        <Divider />

                        <SelectControl
                            label={__("Additional Option", "essential-blocks")}
                            value={videoOptions}
                            options={OPTIONS}
                            onChange={(videoOptions) => setAttributes({ videoOptions })}
                        />

                        {videoOptions == "eb-sticky" && (
                            <>
                                <BaseControl label={__("Sticky Position", "essential-blocks")}>
                                    <ButtonGroup>
                                        {STICKY_POSITION.map((item) => (
                                            <Button
                                                // isLarge
                                                isPrimary={stickyPosition === item.value}
                                                isSecondary={stickyPosition !== item.value}
                                                onClick={() =>
                                                    setAttributes({
                                                        stickyPosition: item.value,
                                                    })
                                                }
                                            >
                                                {item.label}
                                            </Button>
                                        ))}
                                    </ButtonGroup>
                                </BaseControl>
                                <ResponsiveSelectController
                                    baseLabel={__("Visibility", "essential-blocks")}
                                    controlName={stickyVisibility}
                                    options={STICKY_VISIBILITY}
                                    resOption={resOption}
                                />
                            </>
                        )}

                        {videoOptions == "lightbox" && (
                            <>
                                <PanelRow>Placeholder Image</PanelRow>
                                <ImageComponent.GeneralTab
                                    onSelect={({ id, url }) => setAttributes({
                                        placeholderImage: url, placeholderImageId: id
                                    })}
                                    value={placeholderImage}
                                    hasTag={false}
                                    hasCaption={false}
                                    hasStyle={false}
                                    hasLink={false}
                                    showInPanel={false}
                                />

                                <PanelRow>Play Icon</PanelRow>
                                <ToggleControl
                                    label={__("Show Play Icon", "essential-blocks")}
                                    checked={lightboxPlayIcon}
                                    onChange={(lightboxPlayIcon) =>
                                        setAttributes({
                                            lightboxPlayIcon,
                                        })
                                    }
                                />

                                {lightboxPlayIcon && (
                                    <>
                                        <BaseControl label={__("Icon Type", "essential-blocks")}>
                                            <ButtonGroup>
                                                {ICON_TYPE.map((item) => (
                                                    <Button
                                                        // isLarge
                                                        isPrimary={lightboxPlayIconType === item.value}
                                                        isSecondary={
                                                            lightboxPlayIconType !== item.value
                                                        }
                                                        onClick={() =>
                                                            setAttributes({
                                                                lightboxPlayIconType: item.value,
                                                            })
                                                        }
                                                    >
                                                        {item.label}
                                                    </Button>
                                                ))}
                                            </ButtonGroup>
                                        </BaseControl>

                                        {lightboxPlayIconType === "icon" && (
                                            <BaseControl>
                                                <EBIconPicker
                                                    value={lightboxPlayIconlib}
                                                    attributeName={'lightboxPlayIconlib'}
                                                />
                                            </BaseControl>
                                        )}

                                        {lightboxPlayIconType === "image" && (
                                            <ImageComponent.GeneralTab
                                                onSelect={({ id, url }) => setAttributes({
                                                    placeholderPlayIconURL: url, placeholderPlayIconId: id
                                                })}
                                                value={placeholderPlayIconURL}
                                                hasTag={false}
                                                hasCaption={false}
                                                hasStyle={false}
                                                hasLink={false}
                                                showInPanel={false}
                                            />
                                        )}
                                    </>
                                )}
                            </>
                        )}
                    </PanelBody>
                </>
            </InspectorPanel.General>
            <InspectorPanel.Style>
                <>
                    <PanelBody title={__("Video Styles", "essential-blocks")}>
                        {videoOptions != "lightbox" && (
                            <>
                                <ResponsiveRangeController
                                    baseLabel={__("Width", "essential-blocks")}
                                    controlName={VIDEO_WIDTH}
                                    min={1}
                                    max={1000}
                                    step={1}
                                    units={SIZE_UNIT_TYPES}
                                />
                                <BaseControl label={__("Alignment", "essential-blocks")}>
                                    <ButtonGroup>
                                        {ALIGNMENT.map((item) => (
                                            <Button
                                                // isLarge
                                                isPrimary={videoAlignment === item.value}
                                                isSecondary={videoAlignment !== item.value}
                                                onClick={() =>
                                                    setAttributes({
                                                        videoAlignment: item.value,
                                                    })
                                                }
                                            >
                                                {item.label}
                                            </Button>
                                        ))}
                                    </ButtonGroup>
                                </BaseControl>
                            </>
                        )}

                        {videoOptions == "lightbox" && (
                            <>
                                {lightboxPlayIcon && (
                                    <>
                                        <PanelRow>Play Icon</PanelRow>
                                        {lightboxPlayIconType == "icon" && (
                                            <>
                                                <ColorControl
                                                    label={__("Icon Color", "essential-blocks")}
                                                    color={lightboxPlayIconlibColor}
                                                    attributeName={'lightboxPlayIconlibColor'}
                                                />
                                            </>
                                        )}
                                        <ResponsiveRangeController
                                            baseLabel={__("Size", "essential-blocks")}
                                            controlName={PLACEHOLDER_PLAY_ICON_WIDTH}
                                            min={1}
                                            max={400}
                                            step={1}
                                            units={SIZE_UNIT_TYPES}
                                        />
                                    </>
                                )}

                                <PanelRow>Placeholder Image</PanelRow>
                                <ResponsiveRangeController
                                    baseLabel={__("Width", "essential-blocks")}
                                    controlName={PLACEHOLDER_IMAGE_WIDTH}
                                    min={1}
                                    max={1000}
                                    step={1}
                                    units={SIZE_UNIT_TYPES}
                                />
                                <ResponsiveRangeController
                                    baseLabel={__("Height", "essential-blocks")}
                                    controlName={PLACEHOLDER_IMAGE_HEIGHT}
                                    min={1}
                                    max={1000}
                                    step={1}
                                    units={SIZE_UNIT_TYPES}
                                />

                                <Divider />
                            </>
                        )}

                        <BaseControl>
                            <h3 className="eb-control-title">{__("Border", "essential-blocks")}</h3>
                        </BaseControl>
                        <BorderShadowControl
                            controlName={VIDEO_BORDER_SHADOW}
                        // noShadow
                        // noBorder
                        />
                    </PanelBody>

                    {imageOverlay && customPlayIcon && (
                        <PanelBody title={__("Image Overlay", "essential-blocks")} initialOpen={false}>
                            <ResponsiveRangeController
                                baseLabel={__("Play Icon Size", "essential-blocks")}
                                controlName={PLAY_ICON_WIDTH}
                                min={1}
                                max={400}
                                step={1}
                                units={SIZE_UNIT_TYPES}
                            />

                            {placeholderCustomPlayIconType == "icon" && (
                                <>
                                    <ColorControl
                                        label={__("Icon Color", "essential-blocks")}
                                        color={customPlayIconlibColor}
                                        attributeName={'customPlayIconlibColor'}
                                    />
                                </>
                            )}
                        </PanelBody>
                    )}
                    {videoOptions == "lightbox" && (
                        <PanelBody title={__("Lightbox", "essential-blocks")}>
                            <PanelRow>Lightbox</PanelRow>
                            <ColorControl
                                label={__("Overlay Color", "essential-blocks")}
                                color={lightboxBGColor}
                                attributeName={'lightboxBGColor'}
                            />

                            <ResponsiveRangeController
                                baseLabel={__("Content Width", "essential-blocks")}
                                controlName={LIGHTBOX_WIDTH}
                                min={1}
                                max={1000}
                                step={1}
                                units={SIZE_UNIT_TYPES}
                            />
                            <ResponsiveRangeController
                                baseLabel={__("Content Height", "essential-blocks")}
                                controlName={LIGHTBOX_HEIGHT}
                                min={1}
                                max={1000}
                                step={1}
                                units={LIGHTBOX_UNIT_TYPES}
                            />
                            <BorderShadowControl
                                controlName={LIGHTBOX_BORDER_SHADOW}
                                noShadow
                                noBdrHover
                            // noBorder
                            />

                            <PanelRow>Close Icon</PanelRow>
                            <ColorControl
                                label={__("Background Color", "essential-blocks")}
                                color={closeIconBgColor}
                                attributeName={'closeIconBgColor'}
                            />
                            <ColorControl
                                label={__("Color", "essential-blocks")}
                                color={closeIconColor}
                                attributeName={'closeIconColor'}
                            />

                            <ResponsiveRangeController
                                baseLabel={__("Size (PX)", "essential-blocks")}
                                controlName={CLOSE_ICON_WIDTH}
                                min={1}
                                max={100}
                                step={1}
                                noUnits
                            />
                        </PanelBody>
                    )}
                    {videoOptions == "eb-sticky" && (
                        <PanelBody title={__("Sticky", "essential-blocks")} initialOpen={false}>
                            <ResponsiveRangeController
                                baseLabel={__("Width", "essential-blocks")}
                                controlName={STICKY_VIDEO_WIDTH}
                                min={1}
                                max={1000}
                                step={1}
                                units={SIZE_UNIT_TYPES}
                            />
                            <ResponsiveRangeController
                                baseLabel={__("Height", "essential-blocks")}
                                controlName={STICKY_VIDEO_HEIGHT}
                                min={1}
                                max={1000}
                                step={1}
                                units={SIZE_UNIT_TYPES}
                            />

                            <PanelRow>Close Icon</PanelRow>
                            <ColorControl
                                label={__("Color", "essential-blocks")}
                                color={closeIconColor}
                                attributeName={'closeIconColor'}
                            />
                            <ResponsiveRangeController
                                baseLabel={__("Size", "essential-blocks")}
                                controlName={CLOSE_ICON_WIDTH}
                                min={1}
                                max={1000}
                                step={1}
                                units={SIZE_UNIT_TYPES}
                            />
                        </PanelBody>
                    )}
                </>
            </InspectorPanel.Style>
        </InspectorPanel>
    );
}

export default Inspector;
