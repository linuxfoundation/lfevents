import {
	useBlockProps,
} from '@wordpress/block-editor';

const { apiFetch } = wp;

import React, { MouseEventHandler, useCallback, useState } from 'react';
import AsyncSelect from 'react-select/async';
import { components } from 'react-select';
import {
	closestCorners,
	DndContext,
 } from '@dnd-kit/core';
import { restrictToParentElement } from '@dnd-kit/modifiers';
import {
	arrayMove,
	horizontalListSortingStrategy,
	SortableContext,
	useSortable,
} from '@dnd-kit/sortable';
import { CSS } from '@dnd-kit/utilities';

import debounce from 'debounce-promise';

import './editor.scss';

const MultiValue = (props/*: MultiValueProps<ColourOption>*/) => {
	const onMouseDown /*: MouseEventHandler<HTMLDivElement>*/ = (e) => {
		e.preventDefault();
		e.stopPropagation();
	};
	const innerProps = { ...props.innerProps, onMouseDown };
	const { attributes, listeners, setNodeRef, transform, transition } =
		useSortable({
			id: props.data.value,
		});
	const style = {
		// ref: https://github.com/JedWatson/react-select/pull/5212#issuecomment-2096174489
		// transform: CSS.Transform.toString(transform),
		transform: CSS.Translate.toString(transform),
		transition,
	};

	return (
		<div style={style} ref={setNodeRef} {...attributes} {...listeners}>
			<components.MultiValue {...props} innerProps={innerProps} />
		</div>
	);
};

const MultiValueRemove = (props /*: MultiValueRemoveProps<ColourOption>*/) => {
	return (
		<components.MultiValueRemove
			{...props}
			innerProps={{
				onPointerDown: (e) => e.stopPropagation(),
				  ...props.innerProps,
			}}
		  />
	);
};

const prepareOptions = ( list ) => {
	return list.map( ( item ) => {
		return {
			value: item.id,
			label: item.title.rendered,
		};
	} );
};

const searchSpeakers = ( inputValue ) => {
	const value = inputValue.replace( /\W/g, '' );
	return apiFetch( {
		path: `/wp/v2/lfe_speaker/?per_page=100&search=${ value }`,
	} ).then( ( posts ) => {
		return prepareOptions( posts );
	} );
};

const loadOptions = debounce(
	( inputValue ) => searchSpeakers( inputValue ),
	1000,
	{
		leading: false,
	}
);

export default function Edit( { attributes, setAttributes } ) {
	const { speakers } = attributes;

	const [selected, setSelected] = useState(speakers);

	const onChange = (selectedOptions) => {
		setAttributes({
			speakers: [...selectedOptions],
		});

		setSelected([...selectedOptions]);
	};

	const onDragEnd = useCallback((event /*: DragEndEvent */) => {
		const { active, over } = event;

		if (!active || !over) return;

		setSelected((items) => {
			const oldIndex = items.findIndex((item) => item.value === active.id);
			const newIndex = items.findIndex((item) => item.value === over.id);
			let newSpeakers = arrayMove(items, oldIndex, newIndex);

			setAttributes({
				speakers: [...newSpeakers],
			});

			return newSpeakers;
		});
	}, [setSelected]);

	return [
		<div { ...useBlockProps() } key="speakers-block-edit">
			<p>
				<strong>Featured Speakers (each opens with modal):</strong>
			</p>
			{/* ref: https://github.com/JedWatson/react-select/pull/5212#issuecomment-1273870591
			<DndContext modifiers={[restrictToParentElement]} onDragEnd={onDragEnd} collisionDetection={closestCenter}> */}
			<DndContext
				modifiers={[restrictToParentElement]}
				onDragEnd={onDragEnd}
				collisionDetection={closestCorners}
			>
				<SortableContext
					items={selected.map((o) => o.value)}
					// ref: https://github.com/JedWatson/react-select/pull/5212#issuecomment-2096174489
					// strategy={horizontalListSortingStrategy}
				>
					<AsyncSelect
						// distance={4}
						className='SortableList'
						isMulti
						defaultOptions
						loadOptions={ loadOptions }
						helperClass="react-select-draggable"
						// options={selected} // For Select and not AsyncSelect
						value={selected}
						onChange={onChange}
						components={{
							// @ts-ignore We're failing to provide a required index prop to SortableElement
							// MultiValueContainer: DragAndDropContainer,
							MultiValue,
							MultiValueRemove,
						}}
						closeMenuOnSelect={false}
					/>
				</SortableContext>
			</DndContext>
		</div>,
	];
}
