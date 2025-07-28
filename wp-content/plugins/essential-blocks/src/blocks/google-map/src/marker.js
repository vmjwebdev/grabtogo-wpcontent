/**
 * WordPress dependencies
 */
import { useRef, useState } from "@wordpress/element";
import {
    TextControl,
    TextareaControl,
    SelectControl,
    ToggleControl,
} from "@wordpress/components";
import { __ } from "@wordpress/i18n";
import { ImageComponent } from "@essential-blocks/controls";
import { escapeAttribute } from "@wordpress/escape-html";
import { safeHTML } from "@wordpress/dom";

// Style objects
const trashStyle = {
    fontSize: 14,
    borderLeft: "1px solid #b4b4cb",
    lineHeight: "2.5em",
    flex: 2,
    textAlign: "center",
    display: "flex",
    justifyContent: "center",
};

const TrashIcon = ({ position, onDeleteItem }) => (
    <span
        className="eb-social-delete-icon"
        style={trashStyle}
        onClick={() => onDeleteItem(position)}
    >
        <svg
            xmlns="http://www.w3.org/2000/svg"
            x="0"
            y="0"
            enableBackground="new 0 0 512 512"
            version="1.1"
            viewBox="0 0 512 512"
            xmlSpace="preserve"
            style={{ width: 14 }}
        >
            <path
                d="M423.3 86.6H89c-16.8.1-32.2 9.3-40.1 24.1-7.9 14.8-7.1 32.7 2.2 46.8l37.2 55.6V456c0 30.9 25.1 56 56 56h223.9c30.9 0 56-25.1 56-56V213.1l37.2-56c9.1-14 9.8-31.8 1.9-46.5-8.1-14.7-23.4-23.9-40-24zm-198 347c0 13.9-11.3 25.2-25.2 25.2-13.9 0-25.2-11.3-25.2-25.2V220.9c0-13.9 11.3-25.2 25.2-25.2 13.9 0 25.2 11.3 25.2 25.2v212.7zm112 0c0 13.9-11.3 25.2-25.2 25.2-13.9 0-25.2-11.3-25.2-25.2V220.9c0-13.9 11.3-25.2 25.2-25.2 13.9 0 25.2 11.3 25.2 25.2v212.7zM325.8 19.4C309.9 7.1 290.2 0 269.3 0h-26.4c-20.9 0-40.6 7.1-56.5 19.4-11.2 8.7-20.5 20.1-26.9 33.4h193.1c-6.3-13.3-15.6-24.7-26.8-33.4z"
                style={{ fill: "#FF6464" }}
            ></path>
        </svg>
    </span>
);

const SortableItem = ({ marker, map, position, onTitleClick, clickedIndex, onDeleteItem, onMarkerChange, onMarkerSetUpdate }) => {
    const searchRef = useRef(null);

    const initSearch = () => {
        const searchBox = new window.google.maps.places.SearchBox(
            searchRef.current
        );

        searchBox.addListener("places_changed", () => {
            const places = searchBox.getPlaces();

            // This is the new Approach by Jamil
            // Only first Place has been set thought multiple place can be provided by Google
            if (places && 0 < places.length) {
                const place = places[0]
                const latitude = place.geometry.location.lat();
                const longitude = place.geometry.location.lng();
                const latLng = new window.google.maps.LatLng(
                    latitude,
                    longitude
                );

                map.setCenter(latLng);

                onMarkerSetUpdate({
                    ...marker,
                    location: place.formatted_address || place.name,
                    latitude: latitude.toString(),
                    longitude: longitude.toString()
                }, position)
            }

            // **We need to find a way to show all places provided by Google
            // ***
            // if (places && 0 < places.length) {
            //     places.forEach((place) => {
            //         const latitude = place.geometry.location.lat();
            //         const longitude = place.geometry.location.lng();
            //         const latLng = new window.google.maps.LatLng(
            //             latitude,
            //             longitude
            //         );

            //         map.setCenter(latLng);

            //         onMarkerChange(
            //             "location",
            //             place.formatted_address || place.name,
            //             position
            //         );
            //         onMarkerChange(
            //             "latitude",
            //             latitude.toString(),
            //             position
            //         );
            //         onMarkerChange(
            //             "longitude",
            //             longitude.toString(),
            //             position
            //         );
            //     });
            // }
        });
    };

    return (
        <li className="drag-helper">
            <span className="eb-sortable-item">
                <span
                    className="eb-sortable-title"
                    onClick={() => onTitleClick(position)}
                >
                    {marker.title}
                </span>
                <TrashIcon
                    position={position}
                    onDeleteItem={onDeleteItem}
                />
            </span>
            {clickedIndex === position && (
                <div className="eb-google-map-marker-wrapper">
                    <TextControl
                        label={__("Location", "essential-blocks")}
                        value={marker.location}
                        onChange={(value) =>
                            onMarkerChange("location", value, position)
                        }
                        ref={searchRef}
                        onFocus={initSearch}
                    />
                    <TextControl
                        label={__("Latitude", "essential-blocks")}
                        value={marker.latitude}
                        onChange={(value) =>
                            onMarkerChange("latitude", value, position)
                        }
                    />
                    <TextControl
                        label={__("Longitude", "essential-blocks")}
                        value={marker.longitude}
                        onChange={(value) =>
                            onMarkerChange("longitude", value, position)
                        }
                    />
                    <TextControl
                        label={__("Title", "essential-blocks")}
                        value={marker.title}
                        onChange={(value) =>
                            onMarkerChange("title", value, position)
                        }
                    />
                    <TextareaControl
                        label={__("Description", "essential-blocks")}
                        value={marker.content}
                        onChange={(value) =>
                            onMarkerChange("content", value, position)
                        }
                    />
                    <ToggleControl
                        label={__(
                            "Use Custom Marker Image",
                            "essentail-blocks"
                        )}
                        checked={marker.showCustomIcon === "true"}
                        onChange={(value) => {
                            onMarkerChange(
                                "showCustomIcon",
                                value.toString(),
                                position
                            );
                        }}
                    />
                    {marker.showCustomIcon === "true" && (
                        <ImageComponent.GeneralTab
                            onSelect={({ id, url }) => onMarkerChange("imageUrl", url, position)}
                            value={marker.imageUrl}
                            hasTag={false}
                            hasCaption={false}
                            hasStyle={false}
                            hasLink={false}
                            showInPanel={false}
                        />
                    )}
                    {marker.showCustomIcon !== "true" && (
                        <SelectControl
                            label={__("Map Marker", "essential-blocks")}
                            value={
                                marker.icon ||
                                "https://maps.google.com/mapfiles/ms/icons/red-dot.png"
                            }
                            options={[
                                {
                                    label: __("Red", "essential-blocks"),
                                    value:
                                        "https://maps.google.com/mapfiles/ms/icons/red-dot.png",
                                },
                                {
                                    label: __("Blue", "essential-blocks"),
                                    value:
                                        "https://maps.google.com/mapfiles/ms/icons/blue-dot.png",
                                },
                                {
                                    label: __("Yellow", "essential-blocks"),
                                    value:
                                        "https://maps.google.com/mapfiles/ms/icons/yellow-dot.png",
                                },
                                {
                                    label: __("Green", "essential-blocks"),
                                    value:
                                        "https://maps.google.com/mapfiles/ms/icons/green-dot.png",
                                },
                                {
                                    label: __("Orange", "essential-blocks"),
                                    value:
                                        "https://maps.google.com/mapfiles/ms/icons/orange-dot.png",
                                },
                            ]}
                            onChange={(value) =>
                                onMarkerChange("icon", value, position)
                            }
                        />
                    )}
                </div>
            )}
        </li>
    );
}

const SortableMarker = ({ marker, map, setAttributes }) => {

    const [clickedIndex, setClickedIndex] = useState(0);

    // Marker delete callback
    const onDeleteItem = (position) => {
        let newMarker = [...marker].filter(
            (_, index) => position !== index
        );
        setAttributes({ marker: newMarker });
    };

    const onMarkerChange = (key, value, position) => {
        const newMarker = { ...marker[position] };
        const newMarkerList = [...marker];
        newMarkerList[position] = newMarker;
        newMarkerList[position][key] = escapeAttribute(safeHTML(value));
        setAttributes({ marker: [...newMarkerList] });
    };

    const onMarkerSetUpdate = (value, position) => {
        const newMarkerList = [...marker];
        newMarkerList[position] = value;
        setAttributes({ marker: [...newMarkerList] });
    }

    // Expand title when clicked
    const onTitleClick = (position) => {
        let newClickedIndex = clickedIndex === position ? null : position;
        setClickedIndex(newClickedIndex)
    };

    return (
        <ul className="eb-sortable-google-map-marker">
            {marker.map((item, index) => (
                <SortableItem
                    key={`item-${index}`}
                    map={map}
                    index={index}
                    position={index}
                    onTitleClick={onTitleClick}
                    onMarkerChange={onMarkerChange}
                    onMarkerSetUpdate={onMarkerSetUpdate}
                    marker={item}
                    clickedIndex={clickedIndex}
                    onDeleteItem={onDeleteItem}
                />
            ))}
        </ul>
    )
}
export default SortableMarker;



