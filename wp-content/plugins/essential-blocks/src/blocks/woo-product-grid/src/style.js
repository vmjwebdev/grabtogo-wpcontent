import {
    typoPrefix_title,
    typoPrefix_price,
    typoPrefix_sale,
    typoPrefix_desc,
    typoPrefix_btn,
    typoPrefix_viewbtn,
    EBWG_LOAD_MORE_TYPOGRAPHY,
    typoPrefix_cat,
} from "./constants/typographyConstants";
import {
    RATING_ICON_SIZE,
    BTN_BORDER_SHADOW,
    VIEW_BTN_BORDER_SHADOW,
    SALE_BADGE_BORDER,
    WRAPPER_MARGIN,
    WRAPPER_PADDING,
    WRAPPER_BG,
    WRAPPER_BORDER_SHADOW,
    CONTENT_PADDING,
    IMAGE_PADDING,
    PRODUCTS_BORDER_SHADOW,
    GRID_SPACING,
    COLUMNS,
    TITLE_MARGIN,
    PRICE_MARGIN,
    RATING_MARGIN,
    BUTTON_MARGIN,
    VIEW_BUTTON_MARGIN,
    DESC_MARGIN,
    IMG_GAP,
    IMG_HEIGHT,
    IMG_WIDTH,
    IMAGE_BORDER_SHADOW,
    LOADMORE_PADDING,
    LOADMORE_MARGIN,
    LOADMORE_BORDER_SHADOW,
    CAT_SPACE,
} from "./constants";

import {
    softMinifyCssStrings,
    generateTypographyStyles,
    generateResponsiveRangeStyles,
    generateDimensionsControlStyles,
    generateBorderShadowStyles,
    generateBackgroundControlStyles,
    StyleComponent,
} from "@essential-blocks/controls";

export default function Style(props) {
    const { attributes, setAttributes, name, isContentEnabled } = props;

    const {
        resOption,
        blockId,
        blockMeta,
        layout,
        gridPreset,
        titleColor,
        titleHoverColor,
        priceColor,
        salePriceColor,
        ratingColor,
        btnColor,
        btnHoverColor,
        btnBackgroundColor,
        btnBackgroundHoverColor,
        viewbtnColor,
        viewbtnHoverColor,
        viewbtnBackgroundColor,
        viewbtnBackgroundHoverColor,
        saleTextColor,
        saleTextBackgroundColor,
        contentAlignment,
        contentBackgroundColor,
        imageBackgroundColor,
        imageOverlayColor,
        descColor,
        autoHeight,
        backgroundOverlayColor,
        loadMoreOptions,
        classHook,
        loadMoreColor,
        loadMoreBgColor,
        loadMoreHoverColor,
        loadMoreHoverBgColor,
        loadMoreActiveColor,
        loadMoreActiveBgColor,
        enableContents,
        autoFit,
        fitStyles,
        catColor,
        catHoverColor,
    } = attributes;

    const contentAlignmentClass =
        contentAlignment === "left"
            ? "flex-start"
            : contentAlignment === "right"
            ? "flex-end"
            : "center";

    // title typography
    const {
        typoStylesDesktop: titleTypoStylesDesktop,
        typoStylesTab: titleTypoStylesTab,
        typoStylesMobile: titleTypoStylesMobile,
    } = generateTypographyStyles({
        attributes,
        prefixConstant: typoPrefix_title,
    });

    /* TITLE SPACE/MARGIN */
    const {
        dimensionStylesDesktop: titleMarginDesktop,
        dimensionStylesTab: titleMarginTab,
        dimensionStylesMobile: titleMarginMobile,
    } = generateDimensionsControlStyles({
        controlName: TITLE_MARGIN,
        styleFor: "margin",
        attributes,
    });

    // price typography
    const {
        typoStylesDesktop: priceTypoStylesDesktop,
        typoStylesTab: priceTypoStylesTab,
        typoStylesMobile: priceTypoStylesMobile,
    } = generateTypographyStyles({
        attributes,
        prefixConstant: typoPrefix_price,
    });

    /* PRICE SPACE/MARGIN */
    const {
        dimensionStylesDesktop: priceMarginDesktop,
        dimensionStylesTab: priceMarginTab,
        dimensionStylesMobile: priceMarginMobile,
    } = generateDimensionsControlStyles({
        controlName: PRICE_MARGIN,
        styleFor: "margin",
        attributes,
    });

    // rating icon size
    const {
        rangeStylesDesktop: ratingIconSizeDesktop,
        rangeStylesTab: ratingIconSizeTab,
        rangeStylesMobile: ratingIconSizeMobile,
    } = generateResponsiveRangeStyles({
        controlName: RATING_ICON_SIZE,
        property: "font-size",
        attributes,
    });

    /* RATING SPACE/MARGIN */
    const {
        dimensionStylesDesktop: ratingMarginDesktop,
        dimensionStylesTab: ratingMarginTab,
        dimensionStylesMobile: ratingMarginMobile,
    } = generateDimensionsControlStyles({
        controlName: RATING_MARGIN,
        styleFor: "margin",
        attributes,
    });

    // btn border shadow
    const {
        styesDesktop: btnShadowStyesDesktop,
        styesTab: btnShadowStyesTab,
        styesMobile: btnShadowStyesMobile,
        stylesHoverDesktop: btnShadowStylesHoverDesktop,
        stylesHoverTab: btnShadowStylesHoverTab,
        stylesHoverMobile: btnShadowStylesHoverMobile,
        transitionStyle: btnShadowTransitionStyle,
    } = generateBorderShadowStyles({
        controlName: BTN_BORDER_SHADOW,
        attributes,
    });
    const {
        styesDesktop: viewbtnShadowStyesDesktop,
        styesTab: viewbtnShadowStyesTab,
        styesMobile: viewbtnShadowStyesMobile,
        stylesHoverDesktop: viewbtnShadowStylesHoverDesktop,
        stylesHoverTab: viewbtnShadowStylesHoverTab,
        stylesHoverMobile: viewbtnShadowStylesHoverMobile,
        transitionStyle: viewbtnShadowTransitionStyle,
    } = generateBorderShadowStyles({
        controlName: VIEW_BTN_BORDER_SHADOW,
        attributes,
    });

    /* BUTTON SPACE/MARGIN */
    const {
        dimensionStylesDesktop: buttonMarginDesktop,
        dimensionStylesTab: buttonMarginTab,
        dimensionStylesMobile: buttonMarginMobile,
    } = generateDimensionsControlStyles({
        controlName: BUTTON_MARGIN,
        styleFor: "margin",
        attributes,
    });
    const {
        dimensionStylesDesktop: viewbuttonMarginDesktop,
        dimensionStylesTab: viewbuttonMarginTab,
        dimensionStylesMobile: viewbuttonMarginMobile,
    } = generateDimensionsControlStyles({
        controlName: VIEW_BUTTON_MARGIN,
        styleFor: "margin",
        attributes,
    });

    // sale typography
    const {
        typoStylesDesktop: saleTypoStylesDesktop,
        typoStylesTab: saleTypoStylesTab,
        typoStylesMobile: saleTypoStylesMobile,
    } = generateTypographyStyles({
        attributes,
        prefixConstant: typoPrefix_sale,
    });

    // sale badge border
    const {
        styesDesktop: saleShadowStyesDesktop,
        styesTab: saleShadowStyesTab,
        styesMobile: saleShadowStyesMobile,
    } = generateBorderShadowStyles({
        controlName: SALE_BADGE_BORDER,
        attributes,
    });

    /* Wrapper Margin */
    const {
        dimensionStylesDesktop: wrapperMarginDesktop,
        dimensionStylesTab: wrapperMarginTab,
        dimensionStylesMobile: wrapperMarginMobile,
    } = generateDimensionsControlStyles({
        controlName: WRAPPER_MARGIN,
        styleFor: "margin",
        attributes,
    });

    /* Wrapper Padding */
    const {
        dimensionStylesDesktop: wrapperPaddingDesktop,
        dimensionStylesTab: wrapperPaddingTab,
        dimensionStylesMobile: wrapperPaddingMobile,
    } = generateDimensionsControlStyles({
        controlName: WRAPPER_PADDING,
        styleFor: "padding",
        attributes,
    });

    const {
        styesDesktop: wrapperBDShadowDesktop,
        styesTab: wrapperBDShadowTab,
        styesMobile: wrapperBDShadowMobile,
        stylesHoverDesktop: wrapperBDShadowHoverDesktop,
        stylesHoverTab: wrapperBDShadowHoverTab,
        stylesHoverMobile: wrapperBDShadowHoverMobile,
        transitionStyle: wrapperBDShadowTransition,
    } = generateBorderShadowStyles({
        controlName: WRAPPER_BORDER_SHADOW,
        attributes,
    });

    //Generate Background
    const {
        backgroundStylesDesktop: wrapperBackgroundStylesDesktop,
        hoverBackgroundStylesDesktop: wrapperHoverBackgroundStylesDesktop,
        backgroundStylesTab: wrapperBackgroundStylesTab,
        hoverBackgroundStylesTab: wrapperHoverBackgroundStylesTab,
        backgroundStylesMobile: wrapperBackgroundStylesMobile,
        hoverBackgroundStylesMobile: wrapperHoverBackgroundStylesMobile,
        overlayStylesDesktop: wrapperOverlayStylesDesktop,
        hoverOverlayStylesDesktop: wrapperHoverOverlayStylesDesktop,
        overlayStylesTab: wrapperOverlayStylesTab,
        hoverOverlayStylesTab: wrapperHoverOverlayStylesTab,
        overlayStylesMobile: wrapperOverlayStylesMobile,
        hoverOverlayStylesMobile: wrapperHoverOverlayStylesMobile,
        bgTransitionStyle: wrapperBgTransitionStyle,
        ovlTransitionStyle: wrapperOvlTransitionStyle,
    } = generateBackgroundControlStyles({
        attributes,
        controlName: WRAPPER_BG,
    });

    /* content Padding */
    const {
        dimensionStylesDesktop: contentPaddingDesktop,
        dimensionStylesTab: contentPaddingTab,
        dimensionStylesMobile: contentPaddingMobile,
    } = generateDimensionsControlStyles({
        controlName: CONTENT_PADDING,
        styleFor: "padding",
        attributes,
    });

    /* image Padding */
    const {
        dimensionStylesDesktop: imagePaddingDesktop,
        dimensionStylesTab: imagePaddingTab,
        dimensionStylesMobile: imagePaddingMobile,
    } = generateDimensionsControlStyles({
        controlName: IMAGE_PADDING,
        styleFor: "padding",
        attributes,
    });

    const {
        styesDesktop: productBDShadowDesktop,
        styesTab: productBDShadowTab,
        styesMobile: productBDShadowMobile,
        stylesHoverDesktop: productBDShadowHoverDesktop,
        stylesHoverTab: productBDShadowHoverTab,
        stylesHoverMobile: productBDShadowHoverMobile,
        transitionStyle: productBDShadowTransition,
    } = generateBorderShadowStyles({
        controlName: PRODUCTS_BORDER_SHADOW,
        attributes,
    });

    // grid spacing
    const {
        rangeStylesDesktop: gridSpaceingDesktop,
        rangeStylesTab: gridSpaceingTab,
        rangeStylesMobile: gridSpaceingMobile,
    } = generateResponsiveRangeStyles({
        controlName: GRID_SPACING,
        property: "",
        attributes,
    });

    // colomns
    const {
        rangeStylesDesktop: columnNumberDesktop,
        rangeStylesTab: columnNumberTab,
        rangeStylesMobile: columnNumberMobile,
    } = generateResponsiveRangeStyles({
        controlName: COLUMNS,
        property: "",
        attributes,
    });

    // sale typography
    const {
        typoStylesDesktop: descTypoStylesDesktop,
        typoStylesTab: descTypoStylesTab,
        typoStylesMobile: descTypoStylesMobile,
    } = generateTypographyStyles({
        attributes,
        prefixConstant: typoPrefix_desc,
    });

    /* DESC SPACE/MARGIN */
    const {
        dimensionStylesDesktop: descMarginDesktop,
        dimensionStylesTab: descMarginTab,
        dimensionStylesMobile: descMarginMobile,
    } = generateDimensionsControlStyles({
        controlName: DESC_MARGIN,
        styleFor: "margin",
        attributes,
    });

    // image gap
    const {
        rangeStylesDesktop: listImgSpaceDesktop,
        rangeStylesTab: listImgSpaceTab,
        rangeStylesMobile: listImgSpaceMobile,
    } = generateResponsiveRangeStyles({
        controlName: IMG_GAP,
        property: "margin-right",
        attributes,
    });

    // image height
    const {
        rangeStylesDesktop: imageHeightDesktop,
        rangeStylesTab: imageHeightTab,
        rangeStylesMobile: imageHeightMobile,
    } = generateResponsiveRangeStyles({
        controlName: IMG_HEIGHT,
        property: "height",
        attributes,
    });

    // image width
    const {
        rangeStylesDesktop: imageWidthDesktop,
        rangeStylesTab: imageWidthTab,
        rangeStylesMobile: imageWidthMobile,
    } = generateResponsiveRangeStyles({
        controlName: IMG_WIDTH,
        property: "width",
        attributes,
    });

    // button typography
    const {
        typoStylesDesktop: btnTypoStylesDesktop,
        typoStylesTab: btnTypoStylesTab,
        typoStylesMobile: btnTypoStylesMobile,
    } = generateTypographyStyles({
        attributes,
        prefixConstant: typoPrefix_btn,
    });
    const {
        typoStylesDesktop: viewbtnTypoStylesDesktop,
        typoStylesTab: viewbtnTypoStylesTab,
        typoStylesMobile: viewbtnTypoStylesMobile,
    } = generateTypographyStyles({
        attributes,
        prefixConstant: typoPrefix_viewbtn,
    });

    //image border & shadow
    const {
        styesDesktop: imgBDShadowDesktop,
        styesTab: imgBDShadowTab,
        styesMobile: imgBDShadowMobile,
        stylesHoverDesktop: imgBDShadowHoverDesktop,
        stylesHoverTab: imgBDShadowHoverTab,
        stylesHoverMobile: imgBDShadowHoverMobile,
        transitionStyle: imgBDShadowTransition,
    } = generateBorderShadowStyles({
        controlName: IMAGE_BORDER_SHADOW,
        attributes,
    });

    //Load More Controls
    const {
        dimensionStylesDesktop: loadMorePaddingDesktop,
        dimensionStylesTab: loadMorePaddingTab,
        dimensionStylesMobile: loadMorePaddingMobile,
    } = generateDimensionsControlStyles({
        controlName: LOADMORE_PADDING,
        styleFor: "padding",
        attributes,
    });

    const {
        dimensionStylesDesktop: loadMoreMarginDesktop,
        dimensionStylesTab: loadMoreMarginTab,
        dimensionStylesMobile: loadMoreMarginMobile,
    } = generateDimensionsControlStyles({
        controlName: LOADMORE_MARGIN,
        styleFor: "margin",
        attributes,
    });

    const {
        typoStylesDesktop: loadmoreTypoStylesDesktop,
        typoStylesTab: loadmoreTypoStylesTab,
        typoStylesMobile: loadmoreTypoStylesMobile,
    } = generateTypographyStyles({
        attributes,
        prefixConstant: EBWG_LOAD_MORE_TYPOGRAPHY,
        defaultFontSize: 14,
    });

    const {
        styesDesktop: loadMoreBDShadowDesktop,
        styesTab: loadMoreBDShadowTab,
        styesMobile: loadMoreBDShadowMobile,
        stylesHoverDesktop: loadMoreBDShadowHoverDesktop,
        stylesHoverTab: loadMoreBDShadowHoverTab,
        stylesHoverMobile: loadMoreBDShadowHoverMobile,
        transitionStyle: loadMoreBDShadowTransition,
    } = generateBorderShadowStyles({
        controlName: LOADMORE_BORDER_SHADOW,
        attributes,
        noShadow: true,
    });
    //  Category Margin
    const {
        dimensionStylesDesktop: catMarginDesktop,
        dimensionStylesTab: catMarginTab,
        dimensionStylesMobile: catMarginMobile,
    } = generateDimensionsControlStyles({
        controlName: CAT_SPACE,
        styleFor: "margin",
        attributes,
    });

    // section title typography
    const {
        typoStylesDesktop: catTypoStylesDesktop,
        typoStylesTab: catTypoStylesTab,
        typoStylesMobile: catTypoStylesMobile,
    } = generateTypographyStyles({
        attributes,
        prefixConstant: typoPrefix_cat,
    });

    const desktopStyles = `
		.eb-woo-products-wrapper.${blockId} {
			${wrapperMarginDesktop}
			${wrapperPaddingDesktop}
			${wrapperBDShadowDesktop}
			${wrapperBackgroundStylesDesktop}
			${wrapperBDShadowDesktop.trim().length > 0 ? `overflow: hidden;` : ""}
			transition: ${wrapperBDShadowTransition}, ${wrapperBgTransitionStyle};
		}

		.eb-woo-products-wrapper.${blockId}:hover {
			${wrapperBDShadowHoverDesktop}
			${wrapperHoverBackgroundStylesDesktop}
		}

		.eb-woo-products-wrapper.${blockId}:before{
			${wrapperOverlayStylesDesktop}
			transition:${wrapperOvlTransitionStyle};
		}

		.eb-woo-products-wrapper.${blockId}:hover:before{
			${wrapperHoverOverlayStylesDesktop}
		}

		.eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery .eb-woo-product .eb-woo-product-content-wrapper .eb-woo-product-content {
			${contentPaddingDesktop}
			align-items: ${contentAlignmentClass};
			text-align: ${contentAlignment};
		}

		.eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery.grid-preset-1 .eb-woo-products-col,
		.eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery.grid-preset-2 .eb-woo-products-col,
		.eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery.grid-preset-3 .eb-woo-products-col {
			flex: 0 0 ${100 / columnNumberDesktop.replace(/\D/g, "")}%;
			max-width: ${100 / columnNumberDesktop.replace(/\D/g, "")}%;
		}

        .eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery .eb-woo-product-content .eb-woo-product-title {
            ${
                isContentEnabled("title")
                    ? `order: ${enableContents.indexOf("title") + 1};`
                    : ""
            }
        }

		.eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery .eb-woo-product-content .eb-woo-product-title,
		.eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery .eb-woo-product-content .eb-woo-product-title a {
			${titleTypoStylesDesktop}
			${titleMarginDesktop}
			${titleColor ? `color: ${titleColor};` : ""}
		}

		.eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery .eb-woo-product-content .eb-woo-product-title:hover,
		.eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery .eb-woo-product-content .eb-woo-product-title:hover a {
			${titleHoverColor ? `color: ${titleHoverColor};` : ""}
		}

		.eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery .eb-woo-product-content-wrapper .eb-woo-product-content .eb-woo-product-price {
            ${
                isContentEnabled("price")
                    ? `order: ${enableContents.indexOf("price") + 1};`
                    : ""
            }
			${priceTypoStylesDesktop}
			${priceMarginDesktop}
			${priceColor ? `color: ${priceColor};` : ""}
		}

		.eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery .eb-woo-product-content .eb-woo-product-price ins {
			${salePriceColor ? `color: ${salePriceColor};` : ""}
		}

		.eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery .eb-woo-product-content-wrapper .eb-woo-product-content .eb-woo-product-rating-wrapper {
            ${
                isContentEnabled("rating")
                    ? `order: ${enableContents.indexOf("rating") + 1};`
                    : ""
            }
			${ratingMarginDesktop}
		}

		.eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery .eb-woo-product-content-wrapper .eb-woo-product-content .eb-woo-product-rating-wrapper .eb-woo-product-rating {
			${ratingIconSizeDesktop}
			${ratingColor ? `color: ${ratingColor};` : ""}
		}

        .eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery.list-preset-1 .eb-woo-product .eb-woo-product-button-list {
            ${
                isContentEnabled("button")
                    ? `order: ${enableContents.indexOf("button") + 1};`
                    : ""
            }
        }

		.eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery .eb-woo-product .eb-woo-product-overlay .eb-woo-product-button-list a.button,
        .eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery .eb-woo-product .eb-woo-product-overlay .eb-woo-product-button-list a.added_to_cart,
        .eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery .eb-woo-product .eb-woo-product-button-list a.button,
        .eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery .eb-woo-product .eb-woo-product-button-list a.added_to_cart {
			${btnShadowStyesDesktop}
			${buttonMarginDesktop}
			${btnTypoStylesDesktop}
			${btnColor ? `color: ${btnColor};` : ""}
			${btnBackgroundColor ? `background: ${btnBackgroundColor};` : ""}
			transition: all 0.3s, ${btnShadowTransitionStyle};
		}
		${
            gridPreset !== "grid-preset-3"
                ? `.eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery .eb-woo-product .eb-woo-product-overlay .eb-woo-product-button-list a.button:hover,.eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery .eb-woo-product .eb-woo-product-overlay .eb-woo-product-button-list a.added_to_cart:hover, .eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery .eb-woo-product  .eb-woo-product-button-list a.button:hover,.eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery .eb-woo-product  .eb-woo-product-button-list a.added_to_cart:hover {
			${btnShadowStylesHoverDesktop}
			${btnHoverColor ? `color: ${btnHoverColor};` : ""}
			${btnBackgroundHoverColor ? `background: ${btnBackgroundHoverColor};` : ""}
		}`
                : ""
        }


		.eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery.grid-preset-3 .eb-woo-product .eb-woo-product-overlay .eb-woo-product-button-list a.button:hover, .eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery.grid-preset-3 .eb-woo-product .eb-woo-product-overlay .eb-woo-product-button-list a.added_to_cart:hover {
			${btnShadowStylesHoverDesktop}
			${btnHoverColor ? `color: ${btnHoverColor};` : ""}
			${btnBackgroundHoverColor ? `background: ${btnBackgroundHoverColor};` : ""}
		}

		.eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery .eb-woo-product .eb-woo-product-image .eb-woo-product-ribbon {
			${saleTypoStylesDesktop}
			${saleShadowStyesDesktop}
			${saleTextColor ? `color: ${saleTextColor};` : ""}
			${saleTextBackgroundColor ? `background: ${saleTextBackgroundColor};` : ""}
		}

		.eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery .eb-woo-product .eb-woo-product-content-wrapper,
		.eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery.list-preset-1 .eb-woo-product .eb-woo-product-content-wrapper {
			${contentBackgroundColor ? `background: ${contentBackgroundColor};` : ""}
		}

		${
            backgroundOverlayColor
                ? `
		.eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery.grid-preset-3 .eb-woo-product .eb-woo-product-content-wrapper {
			background: ${backgroundOverlayColor};
		}`
                : ""
        }

		.eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery .eb-woo-product .eb-woo-product-image-wrapper .eb-woo-product-image {
			${imageBackgroundColor ? `background-color: ${imageBackgroundColor};` : ""}
			${imagePaddingDesktop}
			${"grid-preset-3" !== gridPreset ? imgBDShadowDesktop : ""}
			transition: ${imgBDShadowTransition};
			overflow: hidden;
		}

		.eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery .eb-woo-product .eb-woo-product-image-wrapper .eb-woo-product-image:hover {
			${"grid-preset-3" !== gridPreset ? imgBDShadowHoverDesktop : ""}
		}

		${
            "grid" === layout &&
            "grid-preset-3" === gridPreset &&
            `.eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery.grid-preset-3 .eb-woo-product {
			${imgBDShadowDesktop}
			transition: ${imgBDShadowTransition};
			overflow: hidden;
		}
		.eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery.grid-preset-3 .eb-woo-product:hover {
			${imgBDShadowHoverDesktop}
		}`
        }

		.eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery .eb-woo-product .eb-woo-product-image-wrapper .eb-woo-product-image a:after {
			background: ${imageOverlayColor}
		}

		.eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery .eb-woo-product .eb-woo-product-image-wrapper .eb-woo-product-image img {
			${imageWidthDesktop}
			${autoHeight ? `height: auto;` : imageHeightDesktop}
            ${autoFit ? `object-fit: ${fitStyles};` : `object-fit: unset;`}
		}

		.eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery.list-preset-1 .eb-woo-product .eb-woo-product-image-wrapper .eb-woo-product-image {
			${imageWidthDesktop}
			${
                autoHeight || imageHeightDesktop === ""
                    ? `height: 100%;`
                    : imageHeightDesktop
            }
		}

		.eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery .eb-woo-product {
			${productBDShadowDesktop}
			${productBDShadowDesktop.trim().length > 0 ? `overflow: hidden;` : ""}
			transition: ${productBDShadowTransition};
		}

		.eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery .eb-woo-product:hover {
			${productBDShadowHoverDesktop}
		}

		.eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery {
			margin-left: -${gridSpaceingDesktop.replace(/\D/g, "") / 2}px;
			margin-right: -${gridSpaceingDesktop.replace(/\D/g, "") / 2}px;
			margin-bottom: -${gridSpaceingDesktop.replace(/\D/g, "")}px;
		}

		.eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery.grid-preset-1 .eb-woo-products-col, .eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery.grid-preset-2 .eb-woo-products-col,
		.eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery.grid-preset-3 .eb-woo-products-col {
			padding-left: ${gridSpaceingDesktop.replace(/\D/g, "") / 2}px;
			padding-right: ${gridSpaceingDesktop.replace(/\D/g, "") / 2}px;
			margin-bottom: ${gridSpaceingDesktop.replace(/\D/g, "")}px;
		}

		.eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery.list-preset-1 .eb-woo-products-col {
			margin-bottom: ${gridSpaceingDesktop.replace(/\D/g, "")}px;
		}

		.eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery.list-preset-1 .eb-woo-product .eb-woo-product-content-wrapper .eb-woo-product-content .eb-woo-product-details {
            ${
                isContentEnabled("description")
                    ? `order: ${enableContents.indexOf("description") + 1};`
                    : ""
            }
			${descTypoStylesDesktop}
			${descMarginDesktop}
			${descColor ? `color: ${descColor};` : ""}
		}

		.eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery.list-preset-1 .eb-woo-product-image-wrapper {
			${listImgSpaceDesktop}
		}

		.eb-woo-products-wrapper.${blockId} .ebpg-pagination {
			text-align: ${loadMoreOptions ? loadMoreOptions.alignment : "center"};
		}
		.eb-woo-products-wrapper.${blockId} .ebpg-pagination button {
			color: ${loadMoreColor};
			background-color: ${loadMoreBgColor};
			${loadMorePaddingDesktop}
			${loadMoreMarginDesktop}
			${loadmoreTypoStylesDesktop}
			${loadMoreBDShadowDesktop}
			transition: ${loadMoreBDShadowTransition};
		}
		.eb-woo-products-wrapper.${blockId} .ebpg-pagination button:hover {
			color: ${loadMoreHoverColor};
			background-color: ${loadMoreHoverBgColor};
			${loadMoreBDShadowHoverDesktop}
		}
		.eb-woo-products-wrapper.${blockId} .ebpg-pagination button.ebpg-pagination-item.active {
			color: ${loadMoreActiveColor};
			background-color: ${loadMoreActiveBgColor};
		}
	`;

    const viewBtnStylesDesktop = `
        .eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery .eb-woo-product .eb-woo-product-overlay .eb-woo-product-button-list a.button.eb-woo-product-detail,
        .eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery .eb-woo-product .eb-woo-product-button-list a.button.eb-woo-product-detail {
			${viewbtnShadowStyesDesktop}
			${viewbuttonMarginDesktop}
			${viewbtnTypoStylesDesktop}
			${viewbtnColor ? `color: ${viewbtnColor};` : ""}
			${viewbtnBackgroundColor ? `background: ${viewbtnBackgroundColor};` : ""}
			transition: all 0.3s, ${viewbtnShadowTransitionStyle};
		}

        ${
            gridPreset !== "grid-preset-3"
                ? `.eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery .eb-woo-product .eb-woo-product-overlay .eb-woo-product-button-list a.button.eb-woo-product-detail:hover,
            .eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery .eb-woo-product  .eb-woo-product-button-list a.button.eb-woo-product-detail:hover {
			${viewbtnShadowStylesHoverDesktop}
			${viewbtnHoverColor ? `color: ${viewbtnHoverColor};` : ""}
			${
                viewbtnBackgroundHoverColor
                    ? `background: ${viewbtnBackgroundHoverColor};`
                    : ""
            }
		}`
                : ""
        }

        .eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery.grid-preset-3 .eb-woo-product .eb-woo-product-overlay .eb-woo-product-button-list a.button.eb-woo-product-detail:hover {
			${viewbtnShadowStylesHoverDesktop}
			${viewbtnHoverColor ? `color: ${viewbtnHoverColor};` : ""}
			${
                viewbtnBackgroundHoverColor
                    ? `background: ${viewbtnBackgroundHoverColor};`
                    : ""
            }
		}

    `;
    const viewBtnStylesTab = `
        .eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery .eb-woo-product .eb-woo-product-overlay .eb-woo-product-button-list a.button.eb-woo-product-detail,
        .eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery .eb-woo-product .eb-woo-product-button-list a.button.eb-woo-product-detail {
			${viewbtnShadowStyesTab}
			${viewbuttonMarginTab}
			${viewbtnTypoStylesTab}
		}

        ${
            gridPreset !== "grid-preset-3"
                ? `.eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery .eb-woo-product .eb-woo-product-overlay .eb-woo-product-button-list a.button.eb-woo-product-detail:hover,
            .eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery .eb-woo-product  .eb-woo-product-button-list a.button.eb-woo-product-detail:hover {
			${viewbtnShadowStylesHoverTab}
		}`
                : ""
        }

        .eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery.grid-preset-3 .eb-woo-product .eb-woo-product-overlay .eb-woo-product-button-list a.button.eb-woo-product-detail:hover {
			${viewbtnShadowStylesHoverTab}
		}

    `;
    const viewBtnStylesMobile = `
        .eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery .eb-woo-product .eb-woo-product-overlay .eb-woo-product-button-list a.button.eb-woo-product-detail,
        .eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery .eb-woo-product .eb-woo-product-button-list a.button.eb-woo-product-detail {
			${viewbtnShadowStyesMobile}
			${viewbuttonMarginMobile}
			${viewbtnTypoStylesMobile}
		}

        ${
            gridPreset !== "grid-preset-3"
                ? `.eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery .eb-woo-product .eb-woo-product-overlay .eb-woo-product-button-list a.button.eb-woo-product-detail:hover,
            .eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery .eb-woo-product  .eb-woo-product-button-list a.button.eb-woo-product-detail:hover {
			${viewbtnShadowStylesHoverMobile}
		}`
                : ""
        }

        .eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery.grid-preset-3 .eb-woo-product .eb-woo-product-overlay .eb-woo-product-button-list a.button.eb-woo-product-detail:hover {
			${viewbtnShadowStylesHoverMobile}
		}

    `;

    const tabStyles = `
		.eb-woo-products-wrapper.${blockId} {
			${wrapperMarginTab}
			${wrapperPaddingTab}
			${wrapperBDShadowTab}
			${wrapperBackgroundStylesTab}
		}

		.eb-woo-products-wrapper.${blockId}:hover {
			${wrapperBDShadowHoverTab}
			${wrapperHoverBackgroundStylesTab}
		}

		.eb-woo-products-wrapper.${blockId}:before{
			${wrapperOverlayStylesTab}
		}

		.eb-woo-products-wrapper.${blockId}:hover:before{
			${wrapperHoverOverlayStylesTab}
		}

		.eb-woo-products-wrapper.${blockId} .ebpg-pagination button {
			${loadMorePaddingTab}
			${loadMoreMarginTab}
			${loadmoreTypoStylesTab}
			${loadMoreBDShadowTab}
		}

		.eb-woo-products-wrapper.${blockId} .ebpg-pagination button:hover {
			${loadMoreBDShadowHoverTab}
		}

		.eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery {
			margin-left: -${gridSpaceingTab.replace(/\D/g, "") / 2}px;
			margin-right: -${gridSpaceingTab.replace(/\D/g, "") / 2}px;
			margin-bottom: -${gridSpaceingTab.replace(/\D/g, "")}px;
		}

		.eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery.grid-preset-1 .eb-woo-products-col,
		.eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery.grid-preset-2 .eb-woo-products-col,
		.eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery.grid-preset-3 .eb-woo-products-col {
			flex: 0 0 ${100 / columnNumberTab.replace(/\D/g, "")}%;
			max-width: ${100 / columnNumberTab.replace(/\D/g, "")}%;
		}

		.eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery .eb-woo-product-content .eb-woo-product-title,
		.eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery .eb-woo-product-content .eb-woo-product-title a {
			${titleTypoStylesTab}
			${titleMarginTab}
		}

		.eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery .eb-woo-product-content-wrapper .eb-woo-product-content .eb-woo-product-price {
			${priceTypoStylesTab}
			${priceMarginTab}
		}

		.eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery .eb-woo-product-content .eb-woo-product-rating-wrapper {
			${ratingMarginTab}
		}

		.eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery .eb-woo-product-content-wrapper .eb-woo-product-content .eb-woo-product-rating-wrapper .eb-woo-product-rating {
			${ratingIconSizeTab}
		}

		.eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery .eb-woo-product .eb-woo-product-content-wrapper .eb-woo-product-content {
			${contentPaddingTab}
		}

		.eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery .eb-woo-product .eb-woo-product-overlay .eb-woo-product-button-list a.button,.eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery .eb-woo-product .eb-woo-product-overlay .eb-woo-product-button-list a.added_to_cart, .eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery .eb-woo-product .eb-woo-product-button-list a.button, .eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery .eb-woo-product .eb-woo-product-button-list a.added_to_cart {
			${btnShadowStyesTab}
			${buttonMarginTab}
			${btnTypoStylesTab}
		}

		.eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery .eb-woo-product .eb-woo-product-overlay .eb-woo-product-button-list a.button:hover,.eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery .eb-woo-product .eb-woo-product-overlay .eb-woo-product-button-list a.added_to_cart:hover, .eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery .eb-woo-product  .eb-woo-product-button-list a.button:hover,.eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery .eb-woo-product  .eb-woo-product-button-list a.added_to_cart:hover {
			${btnShadowStylesHoverTab}
		}

		.eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery .eb-woo-product .eb-woo-product-image .eb-woo-product-ribbon {
			${saleShadowStyesTab}
			${saleTypoStylesTab}
		}

		.eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery .eb-woo-product {
			${productBDShadowTab}
		}

		.eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery .eb-woo-product:hover {
			${productBDShadowHoverTab}
		}

		.eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery .eb-woo-product .eb-woo-product-image-wrapper .eb-woo-product-image {
			${imagePaddingTab}
			${"grid-preset-3" !== gridPreset ? imgBDShadowTab : ""}
		}

		.eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery .eb-woo-product .eb-woo-product-image-wrapper .eb-woo-product-image:hover {
			${"grid-preset-3" !== gridPreset ? imgBDShadowHoverTab : ""}
		}

		${
            "grid" === layout &&
            "grid-preset-3" === gridPreset &&
            `.eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery.grid-preset-3 .eb-woo-product {
			${imgBDShadowTab}
		}
		.eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery.grid-preset-3 .eb-woo-product:hover {
			${imgBDShadowHoverTab}
		}`
        }

		.eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery.list-preset-1 .eb-woo-product .eb-woo-product-image-wrapper .eb-woo-product-image {
        {
			${imageWidthTab}
			${imageHeightTab}
		}

		.eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery {
			margin-left: -${gridSpaceingTab.replace(/\D/g, "") / 2}px;
			margin-right: -${gridSpaceingTab.replace(/\D/g, "") / 2}px;
		}

		.eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery.grid-preset-1 .eb-woo-products-col, .eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery.grid-preset-2 .eb-woo-products-col,
		.eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery.grid-preset-3 .eb-woo-products-col {
			padding-left: ${gridSpaceingTab.replace(/\D/g, "") / 2}px;
			padding-right: ${gridSpaceingTab.replace(/\D/g, "") / 2}px;
			margin-bottom: ${gridSpaceingTab.replace(/\D/g, "")}px;
		}

		.eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery.list-preset-1 .eb-woo-products-col {
			margin-bottom: ${gridSpaceingTab.replace(/\D/g, "")}px;
		}

		.eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery.list-preset-1 .eb-woo-product .eb-woo-product-content-wrapper .eb-woo-product-content .eb-woo-product-details {
			${descTypoStylesTab}
			${descMarginTab}
		}

		.eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery.list-preset-1 .eb-woo-product-image-wrapper {
			${listImgSpaceTab}
		}
	`;

    const mobileStyles = `
		.eb-woo-products-wrapper.${blockId} {
			${wrapperMarginMobile}
			${wrapperPaddingMobile}
			${wrapperBDShadowMobile}
			${wrapperBackgroundStylesMobile}
		}

		.eb-woo-products-wrapper.${blockId}:hover {
			${wrapperBDShadowHoverMobile}
			${wrapperHoverBackgroundStylesMobile}
		}

		.eb-woo-products-wrapper.${blockId}:before{
			${wrapperOverlayStylesMobile}
		}

		.eb-woo-products-wrapper.${blockId}:hover:before{
			${wrapperHoverOverlayStylesMobile}
		}

		.eb-woo-products-wrapper.${blockId} .ebpg-pagination button {
			${loadMorePaddingMobile}
			${loadMoreMarginMobile}
			${loadmoreTypoStylesMobile}
			${loadMoreBDShadowMobile}
		}

		.eb-woo-products-wrapper.${blockId} .ebpg-pagination button:hover {
			${loadMoreBDShadowHoverMobile}
		}

		.eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery {
			margin-left: -${gridSpaceingMobile.replace(/\D/g, "") / 2}px;
			margin-right: -${gridSpaceingMobile.replace(/\D/g, "") / 2}px;
			margin-bottom: -${gridSpaceingMobile.replace(/\D/g, "")}px;
		}

		.eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery.grid-preset-1 .eb-woo-products-col,
		.eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery.grid-preset-2 .eb-woo-products-col,
		.eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery.grid-preset-3 .eb-woo-products-col {
			flex: 0 0 ${100 / columnNumberMobile.replace(/\D/g, "")}%;
			max-width: ${100 / columnNumberMobile.replace(/\D/g, "")}%;
		}

		.eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery .eb-woo-product-content .eb-woo-product-title,
		.eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery .eb-woo-product-content .eb-woo-product-title a {
			${titleTypoStylesMobile}
			${titleMarginMobile}
		}

		.eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery .eb-woo-product-content-wrapper .eb-woo-product-content .eb-woo-product-price {
			${priceTypoStylesMobile}
			${priceMarginMobile}
		}

		.eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery .eb-woo-product-content .eb-woo-product-rating-wrapper {
			${ratingMarginMobile}
		}

		.eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery .eb-woo-product-content-wrapper .eb-woo-product-content .eb-woo-product-rating-wrapper .eb-woo-product-rating {
			${ratingIconSizeMobile}
		}

		.eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery .eb-woo-product .eb-woo-product-content-wrapper .eb-woo-product-content {
			${contentPaddingMobile}
		}

		.eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery .eb-woo-product .eb-woo-product-overlay .eb-woo-product-button-list a.button,.eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery .eb-woo-product .eb-woo-product-overlay .eb-woo-product-button-list a.added_to_cart, .eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery .eb-woo-product .eb-woo-product-button-list a.button, .eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery .eb-woo-product .eb-woo-product-button-list a.added_to_cart {
			${btnShadowStyesMobile}
			${buttonMarginMobile}
			${btnTypoStylesMobile}
		}

		.eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery .eb-woo-product .eb-woo-product-overlay .eb-woo-product-button-list a.button:hover,.eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery .eb-woo-product .eb-woo-product-overlay .eb-woo-product-button-list a.added_to_cart:hover, .eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery .eb-woo-product  .eb-woo-product-button-list a.button:hover,.eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery .eb-woo-product  .eb-woo-product-button-list a.added_to_cart:hover {
			${btnShadowStylesHoverMobile}
		}

		.eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery .eb-woo-product .eb-woo-product-image .eb-woo-product-ribbon {
			${saleShadowStyesMobile}
			${saleTypoStylesMobile}
		}

		.eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery .eb-woo-product {
			${productBDShadowMobile}
		}

		.eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery .eb-woo-product:hover {
			${productBDShadowHoverMobile}
		}

		.eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery .eb-woo-product .eb-woo-product-image-wrapper .eb-woo-product-image {
			${imagePaddingMobile}
			${"grid-preset-3" !== gridPreset ? imgBDShadowMobile : ""}
		}

		.eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery .eb-woo-product .eb-woo-product-image-wrapper .eb-woo-product-image:hover {
			${"grid-preset-3" !== gridPreset ? imgBDShadowHoverMobile : ""}
		}

		${
            "grid" === layout &&
            "grid-preset-3" === gridPreset &&
            `.eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery.grid-preset-3 .eb-woo-product {
			${imgBDShadowMobile}
		}
		.eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery.grid-preset-3 .eb-woo-product:hover {
			${imgBDShadowHoverMobile}
		}`
        }

		.eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery.list-preset-1 .eb-woo-product .eb-woo-product-image-wrapper .eb-woo-product-image {
			${imageWidthMobile}
			${imageHeightMobile}
		}

		.eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery.grid-preset-1 .eb-woo-products-col, .eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery.grid-preset-2 .eb-woo-products-col {
			padding-left: ${gridSpaceingMobile.replace(/\D/g, "") / 2}px;
			padding-right: ${gridSpaceingMobile.replace(/\D/g, "") / 2}px;
			margin-bottom: ${gridSpaceingMobile.replace(/\D/g, "")}px;
		}

		.eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery.list-preset-1 .eb-woo-products-col {
			margin-bottom: ${gridSpaceingMobile.replace(/\D/g, "")}px;
		}

		.eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery.list-preset-1 .eb-woo-product .eb-woo-product-content-wrapper .eb-woo-product-content .eb-woo-product-details {
			${descTypoStylesMobile}
			${descMarginMobile}
		}

		.eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery.list-preset-1 .eb-woo-product-image-wrapper {
			${listImgSpaceMobile}
		}
	`;

    const categoryStylesDesktop = `
        .eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery .eb-woo-product-content .eb-woo-product-category {
            ${
                isContentEnabled("category")
                    ? `order: ${enableContents.indexOf("category") + 1};`
                    : ""
            }
            ${catMarginDesktop}
        }
        .eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery .eb-woo-product-content .eb-woo-product-category .eb-woo-product-category-list li,
        .eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery .eb-woo-product-content .eb-woo-product-category .eb-woo-product-category-list li a {
            ${catTypoStylesDesktop}
            color: ${catColor};

        }
        .eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery .eb-woo-product-content .eb-woo-product-category .eb-woo-product-category-list li:hover,
        .eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery .eb-woo-product-content .eb-woo-product-category .eb-woo-product-category-list li:hover a {
            color: ${catHoverColor};
        }
    `;

    const categoryStylesTab = `
        .eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery .eb-woo-product-content .eb-woo-product-category {
            ${catMarginTab}
        }
        .eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery .eb-woo-product-content .eb-woo-product-category .eb-woo-product-category-list li,
        .eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery .eb-woo-product-content .eb-woo-product-category .eb-woo-product-category-list li a {
            ${catTypoStylesTab}
        }
    `;

    const categoryStylesMobile = `
        .eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery .eb-woo-product-content .eb-woo-product-category {
            ${catMarginMobile}
        }
        .eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery .eb-woo-product-content .eb-woo-product-category .eb-woo-product-category-list li,
        .eb-woo-products-wrapper.${blockId} .eb-woo-products-gallery .eb-woo-product-content .eb-woo-product-category .eb-woo-product-category-list li a {
            ${catTypoStylesMobile}
        }
    `;

    // all css styles for large screen width (desktop/laptop) in strings ⬇
    const desktopAllStyles = softMinifyCssStrings(`
        ${desktopStyles}
        ${viewBtnStylesDesktop}
        ${categoryStylesDesktop}
    `);

    // all css styles for Tab in strings ⬇
    const tabAllStyles = softMinifyCssStrings(`
        ${tabStyles}
        ${viewBtnStylesTab}
        ${categoryStylesTab}
    `);

    // all css styles for Mobile in strings ⬇
    const mobileAllStyles = softMinifyCssStrings(`
        ${mobileStyles}
        ${viewBtnStylesMobile}
        ${categoryStylesMobile}
    `);

    return (
        <>
            <StyleComponent
                attributes={attributes}
                setAttributes={setAttributes}
                desktopAllStyles={desktopAllStyles}
                tabAllStyles={tabAllStyles}
                mobileAllStyles={mobileAllStyles}
                blockName={name}
            />
        </>
    );
}
