/**
 * @package     Gravity PDF Previewer
 * @copyright   Copyright (c) 2020, Blue Liquid Designs
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1
 */

/**
 * Adds a refresh icon to the DOM
 *
 * @since 0.1
 */
export default class {
  /**
   * @param element container The container the icon is appended to
   * @param func callback The function to run when the icon is clicked
   * @param string type The colour of the icon. Black and White are supported. Default to black.
   *
   * @since 0.1
   */
  add (container, callback, type = 'black') {
    /* Get the correct icon */
    const refreshIcon = (type === 'white')
      ? require('svg-url-loader?noquotes!../../images/refresh-white.svg') // eslint-disable-line
      : require('svg-url-loader?noquotes!../../images/refresh.svg') // eslint-disable-line

    /* Create a wrapper */
    this.refresh = document.createElement('div')
    this.refresh.classList.add('gpdf-manually-load-preview')

    /* Create an img DOM element for our icon */
    const refresh = document.createElement('img')
    refresh.setAttribute('src', refreshIcon)
    refresh.setAttribute('style', 'height: 17px;')

    /* Add our link and icon to our wrapper */
    const link = document.createElement('a')
    link.title = PdfPreviewerConstants.refreshTitle
    link.classList.add('refresh-pdf')
    link.appendChild(refresh)

    this.refresh.appendChild(link)

    /* Append the wrapper to the $container */
    container.appendChild(this.refresh)

    /* Listen to onclick event */
    const refreshLink = container.querySelector('a.refresh-pdf')
    refreshLink.onclick = () => callback()
  }

  remove () {
    if (this.refresh) {
      this.refresh.remove()
    }
  }
}
