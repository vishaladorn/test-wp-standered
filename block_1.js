import classnames from 'classnames';
import memoize from 'memize';
import times from 'lodash/times';

(function (blocks, i18n, element, editor, components) {
    const {__} = wp.i18n;
    const {registerBlockType} = wp.blocks;
    const {Fragment} = wp.element;
    const { InspectorControls, InnerBlocks, PanelColorSettings} = wp.editor;
    const {PanelBody, RangeControl} = wp.components;


    /**
     * Register: a Image With Content Block.
     *
     * @link https://wordpress.org/gutenberg/handbook/block-api/
     * @param  {string}   name     Block name.
     * @param  {Object}   settings Block settings.
     * @return {?WPBlock}          The block, if it has been successfully
     *                             registered; otherwise `undefined`.
     */
    registerBlockType('identix/image-with-content', {
        title: __('Image With Content'), //Block title
        // icon: nhBlockIcon.imageWithContent, // Block icon
        // category: 'nhelement',
        keywords: [
            __('image'),
            __('content'),
        ],
        supports: {
            anchor: true,
        },
        attributes: {
            customClass: {
                type: 'string',
                default: '',
            },
            columns: {
                type: 'number',
                default: 2,
            }
        },

        edit(props) {
            const {attributes: {columns, customClass}, className} = props;
            const classes = classnames(className, `md-has-${ columns }-columns`);
            const rightSideBarGetColumnsTemplate = memoize((columns) => {
                return times(columns, n => ['identix/right-column', {id: n + 1}]);
            });
            const ALLOWED_BLOCKS = ['identix/right-column'];
            return (
                <div className={ classes }>
                    <InnerBlocks
                        template={ rightSideBarGetColumnsTemplate(2) }
                        templateLock="all"
                        allowedBlocks={ ALLOWED_BLOCKS }/>
                </div>
            );

        },

        save(props) {
            const {attributes: {customClass}} = props;
            const classes = classnames(`${ customClass }`);

            return (
                <div className={ classes }>
                    <InnerBlocks.Content />
                </div>
            );
        }
    });
    /**
     * Register: a Right Column Block.
     *
     * @link https://wordpress.org/gutenberg/handbook/block-api/
     * @param  {string}   name     Block name.
     * @param  {Object}   settings Block settings.
     * @return {?WPBlock}          The block, if it has been successfully
     *                             registered; otherwise `undefined`.
     */
    registerBlockType('identix/right-column', {
        title: __('Right Column'),
        // icon: nhBlockIcon.imageWithContent, // Block icon
        // category: 'nhelement',
        parent: ['identix/image-with-content'],
        attributes: {
            id: {
                type: 'number',
                default: 1,
            }
        },
        edit: props => {
            const {attributes: {id}} = props;
            const TEMPLATE = [['core/image', {}]];
            return (
                <div className={ id === 1 ? "img-wrap" : "content-wrap" }>
                    { id === 1 ?
                        <InnerBlocks template={ TEMPLATE } allowedBlocks={ 'core/image' } templateLock={ false }/> :
                        <InnerBlocks templateLock={ false }/> }
                </div>
            );
        },

        save({attributes}) {
            const {id} = attributes;
            return (
                <div className={ id === 1 ? "img-wrap" : "content-wrap" }>
                    <InnerBlocks.Content />
                </div>
            );
        }
    });
    /**
     * Register: Progress Bar.
     *
     * @link https://wordpress.org/gutenberg/handbook/block-api/
     * @param  {string}   name     Block name.
     * @param  {Object}   settings Block settings.
     * @return {?WPBlock}          The block, if it has been successfully
     *                             registered; otherwise `undefined`.
     */

    registerBlockType('identix/progress-bar', {
        title: __('Progress Bar'), //Block title
        // icon: nhBlockIcon.progressBar, // Block icon
        // category: 'nhelement',
        keywords: [
            __('progress'),
            __('bar'),
        ],
        attributes: {
            backgroundColor: {
                type: 'string',
                default: '#ccc'
            },
            progressColor: {
                type: 'string',
                default: '#000'
            },
            textColor: {
                type: 'string',
                default: '#fff'
            },
            progressCounter: {
                type: 'number',
                default: 10
            }
        },
        edit(props) {
            const {attributes: {backgroundColor, progressColor, progressCounter, textColor}, setAttributes } = props;
            return [
                (
                    <InspectorControls>
                        <PanelBody title={ __('Progress Range') } initialOpen={ true } className="range-setting">
                            <RangeControl
                                label={ __('Progress Range') }
                                value={ progressCounter }
                                onChange={ ( range ) => setAttributes({progressCounter: range}) }
                                min={ 1 }
                                max={ 100 }
                                step={ 1 }
                            />
                        </PanelBody>
                        <Fragment>
                            <PanelColorSettings
                                title={__('Color Settings') }
                                className="progresscolors"
                                initialOpen={ false }
                                colorSettings={ [
                                    {
                                        label: __('Progress Color'),
                                        value: progressColor,
                                        onChange: (value) => setAttributes({progressColor: value ? value : '#000'}),
                                    },
                                    {
                                        label: __('Background Color'),
                                        value: backgroundColor,
                                        onChange: (value) => setAttributes({backgroundColor: value ? value : '#ccc'}),
                                    },
                                    {
                                        label: __('Text Color'),
                                        value: textColor,
                                        onChange: (value) => setAttributes({textColor: value ? value : '#fff'}),
                                    }
                                ] }
                            />
                        </Fragment>
                    </InspectorControls>
                ),
                (
                    <div className="progress_outer">
                        <div className="progressbar" style={ { backgroundColor: backgroundColor } }>
                            <div className="progress" style={ { backgroundColor: progressColor, width: progressCounter + '%' } } data-value={progressCounter}>
                                <span className="start" style={ { color: textColor } }>{progressCounter}%</span>
                            </div>
                        </div>
                    </div>
                ),
            ]

        },
        save(props) {
            const {attributes: {backgroundColor, progressColor, progressCounter, textColor} } = props;
            return (
                <div className="progress_outer">
                    <div className="progressbar" style={ { backgroundColor: backgroundColor } }>
                        <div className="progress" style={ { backgroundColor: progressColor } } data-value={progressCounter}>
                            <span className="start" style={ { color: textColor } }>{progressCounter}%</span>
                        </div>
                    </div>
                </div>
            );
        }
    });

})(window.wp.blocks, window.wp.i18n, window.wp.element, window.wp.editor, window.wp.components);
