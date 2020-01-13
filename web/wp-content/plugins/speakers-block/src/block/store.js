const { apiFetch } = wp;
const { registerStore } = wp.data;

const actions = {
	setSpeakersList( speakersList ) {
		return {
			type: 'SET_SPEAKERS_LIST',
			speakersList,
		};
	},
	receiveSpeakersList( path ) {
		return {
			type: 'RECEIVE_SPEAKERS_LIST',
			path,
		};
	},
};

const prepareOptions = ( list ) => {
	return list.map( ( item ) => {
		return {
			value: item.id,
			label: item.title.rendered,
		};
	} );
};

registerStore( 'linux/speakers-block', {
	reducer( state = { speakersList: [] }, action ) {
		switch ( action.type ) {
			case 'SET_SPEAKERS_LIST':
				return {
					...state,
					speakersList: action.speakersList,
				};
		}

		return state;
	},

	actions,

	selectors: {
		receiveSpeakersList( state ) {
			const { speakersList } = state;
			return speakersList;
		},
	},

	controls: {
		RECEIVE_SPEAKERS_LIST( action ) {
			return apiFetch( { path: action.path } );
		},
	},

	resolvers: {
		* receiveSpeakersList() {
			const speakersList = yield actions.receiveSpeakersList( '/wp/v2/lfe_speaker/?per_page=80' );
			return actions.setSpeakersList( prepareOptions( speakersList ) );
		},
	},
} );
