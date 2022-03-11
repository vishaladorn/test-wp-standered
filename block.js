
(function (wpI18n, wpBlocks, wpElement, wpEditor, wpComponents) {
    const { __ } = wpI18n;
    const { Component, Fragment } = wpElement;
    const { registerBlockType } = wpBlocks;
    const { InspectorControls } = wpEditor;
    const { PanelBody, ServerSideRender, RangeControl, SelectControl } = wpComponents;

    class DisplayPostBlock extends Component {
        constructor() {
            super(...arguments);
        }
        render() {

            const { attributes: {
                numOfItems,
                postOrder
            },
                setAttributes
            } = this.props;

            return (
                <Fragment>
                    <InspectorControls>
                        <PanelBody title={__('Settings')} initialOpen={true}>
                            <RangeControl
                                label={__('Number of items')}
                                value={numOfItems}
                                min={1}
                                max={100}
                                onChange={ (item) => setAttributes({ numOfItems: parseInt( item ) }) }
                            />
                            <SelectControl
                                label={__('Post Order')}
                                value={ postOrder }
                                onChange={ (order) => setAttributes({ postOrder: order }) }
                                options={ [
                                    { value: 'DESC', label: 'Latest to Oldest' },
                                    { value: 'ASC', label: 'Oldest to Latest' },
                                ] }
                            />
                        </PanelBody>
                    </InspectorControls>
                    <ServerSideRender
                        block = "guest-posts/server-side-render"
                        attributes={ {
                            numOfItems: numOfItems,
                            postOrder: postOrder
                        } }
                    />
                </Fragment >
            );
        }
    }
    const blockAttrs = {
        numOfItems: {
            type: 'number',
            default: 10,
        },
        postOrder: {
            type: 'string',
            default: 'DESC',
        }
    };
    registerBlockType( 'guest-posts/server-side-render', {
        title: __('Guest Post Block', 'plugin or theme guest-posts' ),
        description: __( 'Display Guest Posts', 'plugin or theme guest-posts' ),
        icon: 'lock',
        category: 'common',
        keywords: [__('guest-posts'), __('server')],
        attributes: blockAttrs,
        edit: DisplayPostBlock,
        save() {
            return null;
        },
    });

})(wp.i18n, wp.blocks, wp.element, wp.editor, wp.components);
