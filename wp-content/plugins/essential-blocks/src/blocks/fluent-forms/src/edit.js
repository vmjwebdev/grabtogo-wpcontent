import { __ } from "@wordpress/i18n";
import { memo } from "@wordpress/element";
import { Placeholder, SelectControl } from "@wordpress/components";
import ServerSideRender from "@wordpress/server-side-render";

/**
 * Internal Import
 */
import Inspector from "./inspector";
import { FluentFormIcon } from "./icon";
import Style from "./style";
import defaultAttributes from './attributes'
import {
    FORM_LISTS,
    FORM_FULL_LISTS,
} from "./constants";
import {
    NoticeComponent,
    BlockProps,
    withBlockContext
 } from "@essential-blocks/controls";

const Edit = (props) => {
    const { attributes, setAttributes, isSelected } = props;

    const {
        blockId,
        formId,
        customCheckboxStyle,
        formAlignment,
        showLabels,
        showPlaceholder,
        showErrorMessage,
        classHook,
        cover
    } = attributes;

    const is_fluent_form_active = EssentialBlocksLocalize?.get_plugins['fluentform/fluentform.php']?.active
    // you must declare this variable
    const enhancedProps = {
        ...props,
        blockPrefix: 'eb-fluent-form',
        style: <Style {...props} />
    };

    // get template name for default form
    let template_name =
        FORM_FULL_LISTS &&
        FORM_FULL_LISTS.map((obj) => {
            return {
                attr: obj.attr,
            };
        });

    let wrapperClasses = ["eb-fluent-form-wrapper"];
    // custom checkbox/radio button styles class
    if (customCheckboxStyle) {
        wrapperClasses.push("eb-fluent-custom-radio-checkbox");
    }

    const alignment = {
        left: "eb-fluentform-alignment-left",
        center: "eb-fluentform-alignment-center",
        right: "eb-fluentform-alignment-right",
    };

    if (formAlignment in alignment) {
        wrapperClasses.push(alignment[formAlignment]);
    }

    if (!showLabels) {
        wrapperClasses.push("eb-fluentform-hide-labels");
    }

    if (!showPlaceholder) {
        wrapperClasses.push("eb-fluentform-hide-placeholder");
    }

    if (!showErrorMessage) {
        wrapperClasses.push("eb-fluentform-hide-errormessage");
    }

    if (
        template_name &&
        formId in template_name &&
        template_name[formId].attr === "inline_subscription"
    ) {
        wrapperClasses.push("eb-fluentform-default-subscription");
    }

    return cover.length ? (
        <div>
            <img src={cover} alt="Fluent Forms" style={{ maxWidth: "100%" }} />
        </div>
    ) : (
        <>
            {isSelected && (
                <Inspector
                    key="inspector"
                    attributes={attributes}
                    setAttributes={setAttributes}
                />
            )}
            <BlockProps.Edit {...enhancedProps}>
                <div className={`eb-parent-wrapper eb-parent-${blockId} ${classHook}`}>
                    <div className={`${blockId} ${wrapperClasses.join(" ")}`}>
                        {!is_fluent_form_active && (

                            <NoticeComponent
                                Icon={FluentFormIcon}
                                title={__("Fluent Forms","essential-blocks")}
                                description={
                                    <>
                                        <strong>Fluent Form</strong> is not installed/activated on
                                        your site. Please install and activate{" "}
                                        <a
                                            href={
                                                EssentialBlocksLocalize.eb_admin_url +
                                                `plugin-install.php?s=fluentform&tab=search&type=term`
                                            }
                                            target="_blank"
                                        >
                                            Fluent Form
                                        </a>{" "}
                                        first.
                                    </>
                                }
                            />

                        )}
                        {is_fluent_form_active && !formId && (
                            <Placeholder
                                className={"eb-fluent-form-choose-placeholder"}
                                label={__("Fluent Form", "essential-blocks")}
                            >
                                <SelectControl
                                    value={formId}
                                    options={FORM_LISTS}
                                    onChange={(newFormId) =>
                                        setAttributes({ formId: newFormId })
                                    }
                                />
                            </Placeholder>
                        )}
                        {is_fluent_form_active && formId && (
                            <ServerSideRender
                                className="eb-fluent-form-rendered"
                                block="fluentfom/guten-block"
                                attributes={{ formId: formId }}
                            />
                        )}
                    </div>
                </div>
            </BlockProps.Edit>
        </>
    );
};
export default memo(withBlockContext(defaultAttributes)(Edit))
