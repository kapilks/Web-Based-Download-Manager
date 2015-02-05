setTableHeadWidth()

function setTableHeadWidth()
{
	document.querySelector('#files .tHead').style.width = 
		document.querySelector('.allFiles').offsetWidth + 'px';
}


document.querySelector('#files .tHead').addEventListener( 'click', tableSortingHandler, false );

function tableSortingHandler( event )
{
	var table 			=	 this.nextElementSibling,
		clicked 		=	 event.target,
		allHeadElt 		=	 this.querySelectorAll('th'),
		totalHeadElt 	=	 allHeadElt.length,
		order 			=	 1,
		type 			=	 ['string', 'size', 'int', 'size', 'date'],
		i,
		column;
	
	for( i = 0; i < totalHeadElt; i++ )
	{
		if( clicked == allHeadElt[i] )
		{
			// the column which was clicked
			column = i;
			if( allHeadElt[i].classList.contains('ordered') )
			{
				// reversing the order if the column clicked was previously ordered
				order = allHeadElt[i].classList.contains('asc') ? 0 : 1;
			}
		}

		allHeadElt[i].classList.remove('ordered');
		allHeadElt[i].classList.remove('asc');
		allHeadElt[i].classList.remove('desc');
		
	}

	
	sortTable( table, column, order, type[column]);
	
	//sortTable( table, column, order, type[column]);

	order = ( order == 0 )? 'desc' : 'asc';
	allHeadElt[column].classList.add( 'ordered' );
	allHeadElt[column].classList.add( order );


}

function reOrderTable()
{
	var orderedColumn = document.querySelector('#files .tHead th.ordered');
	
	if( orderedColumn == null )
	{
		console.log('no sorting');
		return;
	}
	orderedColumn.classList.toggle('asc');
	orderedColumn.classList.toggle('desc');
	
	var newEvent = document.createEvent('Event');
	newEvent.initEvent('click',true,true);
	orderedColumn.dispatchEvent(newEvent);
}

// sorting table
function sortTable( table, column, order, type /* optional */ )
{
	var allRows = table.querySelectorAll('tbody tr');
	var totalRows = allRows.length;
	type = type || "string";
	type = type.toLowerCase();
	// converting it to array
	allRows = Array.prototype.slice.call( allRows, 0 );

	allRows.sort( function( row1, row2 )
	{
		var content1 = row1.children[column].textContent;
		var content2 = row2.children[column].textContent;
		//console.log(content1);
		//console.log(content2);
		content1 = changeContent( content1.trim() );
		content2 = changeContent( content2.trim() );
		//console.log(content1);
		//console.log(content2);
		if( order == 1 )
		{
			// ascending order
			if( content1 < content2 )
				return -1;
			else if( content1 > content2 )
				return 1;
			else
				return 0;
		}
		else if( order == 0 )
		{
			// descending order
			if( content1 < content2 )
				return 1;
			else if( content1 > content2 )
				return -1;
			else
				return 0;
		}
	});

	var newTableBody = document.createElement('tbody');
	for( i = 0; i < totalRows; i++ )
	{
		newTableBody.appendChild( allRows[i] );
	}

	// replacing the old body with new body
	table.replaceChild( newTableBody, table.querySelector('tbody') );

	function changeContent( content )
	{
		//console.log(type);
		switch( type )
		{
			case "string"   : 	return content.toLowerCase();
								break;

			case "int"		: 	return parseFloat( content );
								break;

			case "size" 	: 	var units = { byte : 0, kb : 1, mb : 2 },
								num,
								unit;
								
								content = content.toLowerCase();
								if( content.indexOf('ps') >= 0 )
									content = content.slice( 0, content.indexOf('ps') );
								//console.log("Sliced = "+content);
								num 	= parseFloat( content );
								//console.log("num = "+num);
								if(content.indexOf(' ') >= 0)
									unit 	= content.slice( content.indexOf(" ") + 1 );
								else
									unit    = unit || 'byte';
								//console.log("unit = "+unit);
								num 	= num * Math.pow( 1024, units[unit] );
								return num;
								break;
			
			case "date"    	: 	return Date.parse( content );
								break;
		}
	}
}


// context menu

document.addEventListener( 'contextmenu', contextmenuHandler, false );

function contextmenuHandler( event )
{
	event.preventDefault();

	var clicked = event.target;
	//console.log(clicked);
	var allRows = document.querySelectorAll('#files .allFiles tbody tr');
	var totalRows = allRows.length;

	for( i = 0; i < totalRows; i++ )
	{
		if( clicked.parentNode == allRows[i] )
		{
			//console.log("clicked");
			var mouseX = event.clientX;
			var mouseY = event.clientY;
			
			clicked.parentNode.classList.add('context');
/*
			contextMenu = document.querySelector('.contextMenu');
			
			var dataStatus = allRows[i].getAttribute('data-status');
			var className = 'inactive';

			contextMenu.children[0].children[0].classList.remove(className);
			contextMenu.children[0].children[1].classList.remove(className);
			contextMenu.children[0].children[2].classList.remove(className);
			contextMenu.children[0].children[3].classList.remove(className);

			if( dataStatus == 'active' )
			{
				// deactive resume option
				contextMenu.children[0].children[1].classList.add(className);
			}
			else if( dataStatus == 'pause' )
			{
				// deactive stop option
				contextMenu.children[0].children[2].classList.add(className);
			}
			else if( dataStatus == 'done' )
			{
				// deactive resume and stop both
				contextMenu.children[0].children[1].classList.add(className);
				contextMenu.children[0].children[2].classList.add(className);
			}*/
			contextMenu.classList.remove('hidden');
			var menuWidth = contextMenu.offsetWidth;
			var menuHeight = contextMenu.offsetHeight;
			var left = mouseX;
			var top = mouseY;
			if( mouseX + menuWidth >= window.innerWidth )
			{
				left -= menuWidth;
			}
			if( mouseY + menuHeight >= window.innerHeight )
			{
				top -= menuHeight;
			}

			contextMenu.style.cssText = 'position:fixed; top:' + top + 'px; left:' + left + 'px';
			//contextMenu.classList.remove('hidden');
		}
	}

	document.addEventListener( 'click', removeContextMenuHandler, false );
}

function removeContextMenuHandler( event )
{
	var clicked = event.target;
	var contextMenu = document.querySelector('.contextMenu');
	if( !( clicked.parentNode.parentNode == contextMenu ) )
	{
		contextMenu.classList.add('hidden');
		
		document.querySelector('.allFiles tr.context').classList.remove('context');

		document.removeEventListener( 'click', removeContextMenuHandler, false ); 
	}
}

var contextMenu = document.querySelector('.contextMenu');
contextMenu.addEventListener('click',rightClickHandler, false);

function rightClickHandler( event )
{
	// closing centext menu
	//var newEvent = document.createEvent('Event');
	//newEvent.initEvent('click',true,true);
	//document.dispatchEvent( newEvent );
	
	var clicked = event.target;
	console.log(clicked);
	var options = document.querySelectorAll('.contextMenu li');
	if( clicked == options[0] )
	{
		// remove from list
		var row = document.querySelector('.allFiles tr.context');
		var filename = row.getAttribute('title');
		var request = new XMLHttpRequest;
		request.open('GET','php/delete.php?filename='+filename);
		request.send(null);
		request.onreadystatechange = function()
		{
			if(request.readyState === 4 && request.status == 200 )
			{
				if( row.getAttribute('data-status') == 'active')
				{
					var index = getIndex(row.getAttribute('filename'));
					if( index < allDownload.length )
					{
						allDownload[index].active = false;
						allDownload[index].stream.close();
					}
				}
				if( row.classList.contains('active'))
				{
					hideGraph();
					document.querySelector('#speedGraph').removeAttribute('data-active');
					row.classList.remove('active');
				}

				row.parentNode.removeChild(row);


			}
		};
	}
	else if( clicked == options[1])
	{
		// properties
		var lightbox = document.querySelector('.lightbox');
		lightbox.classList.remove('hidden');
		var filename = document.querySelector('.allFiles tr.context').getAttribute('title');

		var request = new XMLHttpRequest;
		request.open('GET','php/properties.php?filename='+filename)
		request.send(null);
		request.onreadystatechange = function( event )
		{
			if(request.readyState === 4 && request.status == 200 )
			{
				var response = request.response;
				
				lightbox.querySelector('.properties').innerHTML = response;
				lightbox.querySelector('.properties').classList.remove('hidden');

			}
		};


	}
}
// category files

document.querySelector('#category').addEventListener('click', categoryHandler, false );

function categoryHandler( event )
{
	var clicked = event.target;

	if( clicked.parentNode.parentNode == this )
	{
		// expander
		//console.log('expander');
		clicked.parentNode.nextElementSibling.classList.toggle('zeroHeight');
		if( clicked.parentNode.nextElementSibling.classList.contains('zeroHeight') )
		{
			clicked.parentNode.nextElementSibling.style.height = 0;
			clicked.textContent = '+';
		}
		else
		{
			clicked.parentNode.nextElementSibling.style.cssText = '';
			clicked.textContent = '-';
		}
	}
	else if( clicked.parentNode == this )
	{
		// main heading clicked
		console.log('main heading');
		//if( !clicked.classList.contains('active') )
		//{
			var allCategory = document.querySelectorAll('#category .eachCategory');
			allCategory[0].classList.remove('active');
			allCategory[1].classList.remove('active');
			allCategory[2].classList.remove('active');

			clicked.classList.add('active');

			var allRows = document.querySelectorAll('#files .allFiles tbody tr');
			var totalRows = allRows.length;
			var i;
			for( i = 0; i < totalRows; i++ )
			{
				allRows[i].classList.remove('hidden');
			}

			if( clicked.classList.contains('completed') )
			{
				for( i = 0; i < totalRows; i++ )
				{
					if( allRows[i].getAttribute('data-status') != 'done' )
					{
						allRows[i].classList.add('hidden');
					}
				}
			}
			else if( clicked.classList.contains('notCompleted') )
			{
				for( i = 0; i < totalRows; i++ )
				{
					if( allRows[i].getAttribute('data-status') == 'done' )
					{
						allRows[i].classList.add('hidden');
					}
				}
			}

			
		//}
	}
	else if( clicked.parentNode.parentNode.parentNode == this )
	{
		// sub category clicked
		console.log('sub category');
		var subCategory = clicked.classList[0];
		console.log(subCategory);
		// dispatch click event on main heading
		var newEvent = document.createEvent('Event');
		newEvent.initEvent( 'click', true, false );
		clicked.parentNode.parentNode.previousElementSibling.dispatchEvent( newEvent );
		
		var allRows = document.querySelectorAll('#files .allFiles tbody tr');
		var totalRows = allRows.length;
		var i;

		for( i = 0; i < totalRows; i++ )
		{
			if( !allRows[i].classList.contains( subCategory ) )
			{
				allRows[i].classList.add('hidden');
			}
		}
	}

	// chnging head width
	setTableHeadWidth();
}