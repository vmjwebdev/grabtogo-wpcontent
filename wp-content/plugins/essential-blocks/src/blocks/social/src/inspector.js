/**
 * WordPress dependencies
 */
import { __ } from "@wordpress/i18n";
import { useEffect } from "@wordpress/element";
import {
    PanelBody,
    SelectControl,
    ToggleControl,
    RangeControl,
    BaseControl,
    Button,
    ButtonGroup,
    TextControl
} from "@wordpress/components";

/**
 * Internal dependencies
 */

import {
    ResponsiveRangeController,
    ColorControl,
    BorderShadowControl,
    SortControl,
    ResetControl,
    EBIconPicker,
    InspectorPanel
} from "@essential-blocks/controls";

import objAttributes from "./attributes";

import {
    rangeIconSize,
    rangeIconPadding,
    rangeIconDistance,
    rangeIconRowGap,
    sclDeviderPosRight,
} from "./constants/rangeNames";

import {
    tmbWrapMarginConst,
    tmbWrapPaddingConst,
} from "./constants/dimensionsConstants";

import { WrpBgConst } from "./constants/backgroundsConstants";

import {
    WrpBdShadowConst,
    prefixSocialBdShadow,
} from "./constants/borderShadowConstants";

import { IconsHzAligns, HOVER_EFFECT, ICON_SHAPE } from "./constants";

function Inspector({ attributes, setAttributes }) {
    const {
        resOption,
        socialDetails,
        iconsJustify,
        isIconsDevider,
        icnsDevideColor,
        icnSepW,
        icnSepH,
        hvIcnColor,
        hvIcnBgc,
        icnEffect,
        iconShape,
        textShadowColor,
        textHOffset,
        textVOffset,
        blurRadius,
        showLinkNewTab
    } = attributes;

    //
    useEffect(() => {
        const newSclDtails = socialDetails.map((item) => ({
            ...item,
            isExpanded: false,
        }));
        setAttributes({ socialDetails: newSclDtails });
    }, []);

    const onShapeChange = (value) => {
        switch (value) {
            case "rounded":
                setAttributes({
                    iconShape: value,
                    sclBdSd_Rds_Bottom: "10",
                    sclBdSd_Rds_Left: "10",
                    sclBdSd_Rds_Right: "10",
                    sclBdSd_Rds_Top: "10",
                    sclBdSd_Rds_Unit: "px",
                    sclBdSd_Rds_isLinked: true,
                });
                break;

            case "circular":
                setAttributes({
                    iconShape: value,
                    sclBdSd_Rds_Bottom: "50",
                    sclBdSd_Rds_Left: "50",
                    sclBdSd_Rds_Right: "50",
                    sclBdSd_Rds_Top: "50",
                    sclBdSd_Rds_Unit: "%",
                    sclBdSd_Rds_isLinked: true,
                });
                break;

            case "square":
                setAttributes({
                    iconShape: value,
                    sclBdSd_Rds_Bottom: undefined,
                    sclBdSd_Rds_Left: undefined,
                    sclBdSd_Rds_Right: undefined,
                    sclBdSd_Rds_Top: undefined,
                    sclBdSd_Rds_Unit: "px",
                    sclBdSd_Rds_isLinked: true,
                });
                break;

            default:
                break;
        }
    };

    const ucFirst = (string) =>
        string
            .split("_")
            .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
            .join(" ");

    const onSocialProfileAdd = () => {
        const socialDetails = [
            ...attributes.socialDetails,
            {
                title: "Behance",
                icon: "fab fa-behance",
                color: "",
                bgColor: "",
                link: "",
                linkOpenNewTab: false,
                isExpanded: false,
            },
        ];

        setAttributes({ socialDetails });
    };

    const getSocialDetailsComponents = () => {
        const onProfileChange = (key, value, position) => {
            const newSocialDetail = { ...attributes.socialDetails[position] };
            const newSocialDetails = [...attributes.socialDetails];
            newSocialDetails[position] = newSocialDetail;

            if (Array.isArray(key)) {
                key.map((item, index) => {
                    newSocialDetails[position][item] = value[index];
                });
            } else {
                newSocialDetails[position][key] = value;
            }

            setAttributes({ socialDetails: newSocialDetails });
        };

        return attributes.socialDetails.map((each, i) => (
            <div key={i}>
                <EBIconPicker
                    title={__("Social Media", "essential-blocks")}
                    value={each.icon || null}
                    onChange={(value) => onProfileChange('icon', value, i)}
                />
                <TextControl
                    label={__("Title", "essential-blocks")}
                    value={each.title}
                    onChange={(value) => onProfileChange('title', value, i)}
                />
                <ColorControl
                    label={__("Icon Color", "essential-blocks")}
                    color={each.color}
                    onChange={(value) => onProfileChange('color', value, i)}
                />
                <ColorControl
                    label={__("Icon Background", "essential-blocks")}
                    color={each.bgColor}
                    onChange={(value) => onProfileChange('bgColor', value, i)}
                />
                <TextControl
                    label={__("URL", "essential-blocks")}
                    value={each.link}
                    onChange={(value) => onProfileChange('link', value, i)}
                    help={__(
                        "Use https or http",
                        "essential-blocks"
                    )}
                />
                {showLinkNewTab && (
                    <ToggleControl
                        label={__("Open in New Tab", "essential-blocks")}
                        checked={each.linkOpenNewTab}
                        onChange={(value) => onProfileChange('linkOpenNewTab', value, i)}
                    />
                )}
            </div>
        ))
    }

    return (
        <InspectorPanel advancedControlProps={{
            marginPrefix: tmbWrapMarginConst,
            paddingPrefix: tmbWrapPaddingConst,
            backgroundPrefix: WrpBgConst,
            borderPrefix: WrpBdShadowConst,
            hasMargin: true
        }}>

            <InspectorPanel.General>
                <InspectorPanel.PanelBody
                    title={__("Social Profiles", "essential-blocks")}
                    initialOpen={true}
                >
                    <>
                        <ToggleControl
                            label={__("Show link in new tab", "essential-blocks")}
                            checked={showLinkNewTab}
                            onChange={() =>
                                setAttributes({
                                    showLinkNewTab: !showLinkNewTab,
                                    socialDetails: [...socialDetails]
                                })
                            }
                        />
                        <SortControl
                            items={socialDetails.map((each, i) => ({ ...each, label: ucFirst((each.icon || " ").replace(/^fa[bsr] fa-|^dashicons-/i, "")) }))}
                            labelKey={'label'}
                            onSortEnd={socialDetails => setAttributes({ socialDetails })}
                            onDeleteItem={index => {
                                setAttributes({ socialDetails: attributes.socialDetails.filter((each, i) => i !== index) })
                            }}
                            hasSettings={true}
                            settingsComponents={getSocialDetailsComponents()}
                            hasAddButton={true}
                            onAddItem={onSocialProfileAdd}
                            addButtonText={__(
                                "Add Social Profile",
                                "essential-blocks"
                            )}
                        />
                    </>
                </InspectorPanel.PanelBody>
            </InspectorPanel.General>
            <InspectorPanel.Style>
                <>
                    <InspectorPanel.PanelBody
                        title={__("Icons Styles", "essential-blocks")}
                        initialOpen={true}
                    >
                        <BaseControl label={__("Icon Shape", "essential-blocks")}>
                            <ButtonGroup>
                                {ICON_SHAPE.map((item, index) => (
                                    <Button
                                        key={index}
                                        isSecondary={iconShape !== item.value}
                                        isPrimary={iconShape === item.value}
                                        onClick={() => onShapeChange(item.value)}
                                    >
                                        {item.label}
                                    </Button>
                                ))}
                            </ButtonGroup>
                        </BaseControl>

                        <BaseControl
                            id="eb-team-icons-alignments"
                            label="Social Icons Horizontal Alignments"
                        >
                            <SelectControl
                                // label={__("Icons Horizontal Alignment", "essential-blocks")}
                                value={iconsJustify}
                                options={IconsHzAligns}
                                onChange={(iconsJustify) =>
                                    setAttributes({ iconsJustify })
                                }
                            />
                        </BaseControl>

                        <ResponsiveRangeController
                            noUnits
                            baseLabel={__("Size", "essential-blocks")}
                            controlName={rangeIconSize}
                            min={5}
                            max={300}
                            step={1}
                        />

                        <ResponsiveRangeController
                            noUnits
                            baseLabel={__("Padding", "essential-blocks")}
                            controlName={rangeIconPadding}
                            min={0}
                            max={6}
                            step={0.1}
                        />

                        <ResponsiveRangeController
                            noUnits
                            baseLabel={__("Spacing", "essential-blocks")}
                            controlName={rangeIconDistance}
                            min={0}
                            max={100}
                            step={1}
                        />

                        <ResponsiveRangeController
                            noUnits
                            baseLabel={__("Rows Gap", "essential-blocks")}
                            controlName={rangeIconRowGap}
                            min={0}
                            max={100}
                            step={1}
                        />

                        <label
                            style={{
                                display: "block",
                                margin: "-20px 0 20px",
                            }}
                        >
                            <i>
                                N.B. 'Rows Gap' is used when you have multiple rows of
                                social profiles. Normally in case of only one row, it's
                                not needed
                            </i>
                        </label>

                        <ToggleControl
                            label={__("Icons Devider", "essential-blocks")}
                            checked={isIconsDevider}
                            onChange={() =>
                                setAttributes({
                                    isIconsDevider: !isIconsDevider,
                                })
                            }
                        />

                        {isIconsDevider && (
                            <>
                                <ColorControl
                                    label={__("Color", "essential-blocks")}
                                    color={icnsDevideColor}
                                    attributeName={'icnsDevideColor'}
                                />

                                <RangeControl
                                    label={__("Width", "essential-blocks")}
                                    value={icnSepW}
                                    onChange={(icnSepW) => setAttributes({ icnSepW })}
                                    step={1}
                                    min={1}
                                    max={50}
                                />

                                <RangeControl
                                    label={__("Height", "essential-blocks")}
                                    value={icnSepH}
                                    onChange={(icnSepH) => setAttributes({ icnSepH })}
                                    step={1}
                                    min={1}
                                    max={300}
                                />

                                <ResponsiveRangeController
                                    baseLabel={__(
                                        "Position From Right",
                                        "essential-blocks"
                                    )}
                                    controlName={sclDeviderPosRight}
                                    min={0}
                                    max={80}
                                    step={1}
                                />
                            </>
                        )}

                        <ColorControl
                            label={__("Hover Color", "essential-blocks")}
                            color={hvIcnColor}
                            attributeName={'hvIcnColor'}
                        />

                        <ColorControl
                            label={__("Hover Background", "essential-blocks")}
                            color={hvIcnBgc}
                            attributeName={'hvIcnBgc'}
                        />

                        <SelectControl
                            label={__("Icon Hover Effect", "essential-blocks")}
                            value={icnEffect}
                            options={HOVER_EFFECT}
                            // onChange={(preset) => setAttributes({ preset })}
                            onChange={(icnEffect) => {
                                setAttributes({ icnEffect });
                            }}
                        />
                    </InspectorPanel.PanelBody>

                    <InspectorPanel.PanelBody
                        title={__("Icons Border & Box-Shadow")}
                        initialOpen={false}
                    >
                        <BorderShadowControl
                            controlName={prefixSocialBdShadow}
                        // noShadow
                        // noBorder
                        />
                    </InspectorPanel.PanelBody>
                    <InspectorPanel.PanelBody
                        title={__("Icons Shadow", "essential-blocks")}
                        initialOpen={false}
                    >
                        <ColorControl
                            label={__("Shadow Color", "essential-blocks")}
                            color={textShadowColor}
                            attributeName={'textShadowColor'}
                        />

                        <ResetControl
                            onReset={() => setAttributes({ textHOffset: undefined })}
                        >
                            <RangeControl
                                label={__("Horizontal Offset", "essential-blocks")}
                                value={textHOffset}
                                onChange={(newValue) =>
                                    setAttributes({ textHOffset: newValue })
                                }
                                min={0}
                                max={100}
                            />
                        </ResetControl>

                        <ResetControl
                            onReset={() => setAttributes({ textVOffset: undefined })}
                        >
                            <RangeControl
                                label={__("Vertical Offset", "essential-blocks")}
                                value={textVOffset}
                                onChange={(newValue) =>
                                    setAttributes({ textVOffset: newValue })
                                }
                                min={0}
                                max={100}
                            />
                        </ResetControl>

                        <ResetControl
                            onReset={() => setAttributes({ blurRadius: undefined })}
                        >
                            <RangeControl
                                label={__("Blur Radius", "essential-blocks")}
                                value={blurRadius}
                                onChange={(newValue) =>
                                    setAttributes({ blurRadius: newValue })
                                }
                                min={0}
                                max={100}
                            />
                        </ResetControl>
                    </InspectorPanel.PanelBody>
                </>
            </InspectorPanel.Style>
        </InspectorPanel>

    );
}
export default Inspector;
