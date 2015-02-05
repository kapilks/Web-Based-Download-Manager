var youtubeForm = document.querySelector('.youtube .youtubeForm');
youtubeForm.addEventListener('submit',youtubeHandler,false);

function youtubeHandler( event )
{
	event.preventDefault();

	var form = this;
	// loading gif visible
	form.nextElementSibling.classList.remove( 'hidden' );
	
	// changing height of div
	var youtubeBlock = this.parentNode.parentNode;
	youtubeBlock.style.height = bufferHeight( youtubeBlock )+'px';

	var url = form.querySelector('input[name="newUrl"]').value;

	var request = new XMLHttpRequest;
	request.open('GET','php/youtube.php?url='+url);
	request.send(null);

	request.onreadystatechange = function()
	{
		if( request.readyState === 4 && request.status == 200 )
		{
			var response = request.response;
			var contentType = request.getResponseHeader('Content-Type');
			console.log(contentType);
			form.nextElementSibling.classList.add('hidden');
			if( contentType == 'text/html' )
			{//console.log(request.getAllResponseHeaders());
				var detail = document.querySelector('.youtube .videoDetail');
				detail.innerHTML = response;

				
				 
			}
			else if(contentType == 'application/json')
			{
				// error
				var data = JSON.parse(response);
				displayDialog('error',data.error);
			}
			youtubeBlock.style.height = bufferHeight( youtubeBlock )+'px';
		}
	};
}



var yDetail = document.querySelector('.youtube .videoDetail');
yDetail.addEventListener('click',yDownloadHandler,false);

function yDownloadHandler( event )
{
	var clicked = event.target;

	if( this == clicked.parentNode.parentNode.parentNode.parentNode)
	{
		// download file
		var youtubeBlock = document.querySelector('.allMenu .youtube').parentNode;
		youtubeBlock.classList.add('zeroHeight');
		youtubeBlock.style.cssText = "";
		var downloadUrl = clicked.parentNode.parentNode.getAttribute('data-url');

		var newEvent = document.createEvent('Event');
		newEvent.initEvent('click',true,true);
		document.querySelector('.menuOptions [data-menu="add"]').dispatchEvent(newEvent);

		document.querySelector('.allMenu .add input[name="newUrl"]').value = downloadUrl;
		

		console.log( downloadUrl);
	}
}
