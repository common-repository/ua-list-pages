var el = wp.element.createElement;
window.wp.blocks.registerBlockType( 'ua-list-pages/block', {
    title: 'List Pages',
    description:'Display a list of child pages.',
    icon: 'excerpt-view',
    category: 'widgets',
    edit: function( props ) {
        var filterOptions = [];
        const catOptions = wp.data.select('core').getEntityRecords('taxonomy', 'category');
		if( catOptions ) {
			catOptions.forEach((o) => {
				filterOptions.push({id:o.id, name:o.name});
			});
		} else {
			filterOptions.push( { value: 0, label: 'Loading...' } )
        }
        return el(wp.element.Fragment, null,
            el( window.wp.serverSideRender, {
                block: 'ua-list-pages/block',
                attributes: props.attributes,
            } ),
            el(wp.blockEditor.InspectorControls, null,
                el(wp.components.PanelBody, { title: 'Options' },
                    el(wp.components.SelectControl, {
                        key: 'postType',
                        label: 'Post Type',
                        value: props.attributes.postType || 'post',
                        options: [{ label:'Post', value:'post' }, { label:'Page', value:'page' }],
                        onChange: function(value) { props.setAttributes( { postType: value } ); },
                    }),
                    el(wp.components.QueryControls, {
                        numberOfItems: props.attributes.postsToShow || 5,
                        order: props.attributes.order,
                        orderBy: props.attributes.orderBy,
                        onOrderByChange: function(value) { props.setAttributes( { orderBy: value } ); },
                        onOrderChange: function(value) { props.setAttributes( { order: value } ); },
                        onNumberOfItemsChange: function(value) { props.setAttributes( { postsToShow: value } ); },

                        selectedCategoryId: props.attributes.postType == 'post' ? props.attributes.categories : null,
                        categoriesList: props.attributes.postType == 'post' ? filterOptions : null,
                        onCategoryChange: props.attributes.postType == 'post' ? function(value) { props.setAttributes( { categories: '' !== value ? value : undefined }); } : null,
                    }),
                ),
                el(wp.components.PanelBody, { title: 'Layout' },
                    el(wp.components.SelectControl, {
                        key: 'postLayout',
                        label: 'Layout',
                        value: props.attributes.postLayout || 'list',
                        options: [{ label:'Grid', value:'grid' }, { label:'List', value:'list' }],
                        onChange: function(value) { props.setAttributes( { postLayout: value } );},
                    }),
                    el(wp.components.RangeControl, {
                        key: 'columns',
                        label: 'Columns',
                        value: props.attributes.columns || 3,
                        min: 2, max: 6,
                        onChange: function(value) { props.setAttributes( { columns: value } );},
                    }),
                    el(wp.components.SelectControl, {
                        key: 'align',
                        label: 'Text Align',
                        options: [{ label:'Default', value:'' }, { label:'Left', value:'left' }, { label:'Center', value:'center' }, { label:'Right', value:'right' }, { label:'Wide', value:'wide' }, { label:'Full', value:'full' }],
                        value: props.attributes.align || 'left',
                        onChange: function(value) { props.setAttributes( { align: value } );},
                    }),
                    el(wp.components.SelectControl, {
                        key: 'positions',
                        label: 'Element Layout', //'TIMC', 'ITMC', 'TICM', 'ITCM' 
                        options: [{ label:'Title,Image,Date,Content', value:'TIDC' }, { label:'Image,Title,Date,Content', value:'ITDC' }, { label:'Title,Image,Content,Date', value:'TICD' }, { label:'Image,Title,Content,Date', value:'ITCD' }],
                        value: props.attributes.positions || 'top',
                        onChange: function(value) { props.setAttributes( { positions: value } );},
                    }),
                ),
                el(wp.components.PanelBody, { title: 'Content' },
                    el(wp.components.SelectControl, {
                        key: 'imageSize',
                        label: 'Display Image',
                        options: [ { label:'No Image', value:'' }, { label:'Tiny', value:'50x50' }, { label:'Thumbnail', value:'thumbnail' }, { label:'Small', value:'small' }, { label:'Medium', value:'medium' }, { label:'Large', value:'large' } ],
                        value: props.attributes.imageSize,
                        onChange: function(value) { props.setAttributes( { imageSize: value } );},
                    }),
                    el(wp.components.CheckboxControl, {
                        key: 'displayPostTitle',
                        label: 'Display Post Title',
                        checked: props.attributes.displayPostTitle,
                        onChange: function(value) { props.setAttributes( { displayPostTitle: value } );},
                    }),
                    el(wp.components.CheckboxControl, {
                        key: 'displayPostDate',
                        label: 'Display Post Date',
                        checked: props.attributes.displayPostDate || false,
                        onChange: function(value) { props.setAttributes( { displayPostDate: value } );},
                    }),
                    el(wp.components.CheckboxControl, {
                        key: 'displayPostContent',
                        label: 'Display Post Content',
                        checked: props.attributes.displayPostContent,
                        onChange: function(value) { props.setAttributes( { displayPostContent: value } );},
                    }),
                    el(wp.components.RadioControl, {
                        key: 'displayPostContentRadio',
                        label: "Show:",
                        selected:  props.attributes.displayPostContentRadio,
                        options: [{ label: 'Excerpt', value: 'excerpt' }, { label: 'Full Post', value: 'full_post' }],
                        onChange: function(value) { props.setAttributes( { displayPostContentRadio: value } );},
                    }),
                    props.attributes.displayPostContentRadio == 'excerpt' ? 
                    el(wp.components.RangeControl, {
                        key: 'excerptLength',
                        label: 'Excerpt Length',
                        value: props.attributes.excerptLength || 55,
                        min: 0, max: 200,
                        onChange: function(value) { props.setAttributes( { excerptLength: value } );},
                    }) : null,
                )
            )
        );
    },
} );

