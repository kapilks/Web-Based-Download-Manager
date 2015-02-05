var canvas = document.getElementsByTagName('canvas')[0];
var ctx = canvas.getContext('2d');

// have to store 15 points cordinate
var Graph = function( gridWidth, gridHeight, scaleX, scaleY, color, lineWidth, unit )
	{
		// detail of grid and scales
		// gridHeight pixel = scaleY unit
		// gridWidth pixel = scaleX unit
		var config = {};
		config.gridWidth = gridWidth;
		config.gridHeight = gridHeight;
		config.scaleX = scaleX;
		config.scaleY = scaleY;
		config.unit  = unit;
		this.config = config;

		// speed to pixel conversion factor
		this.speedToPixel = gridHeight / scaleY;

		// pixel to speed conversion factor
		this.pixelToSpeed = scaleY / gridHeight;

		// color of the line joining the plotted points
		this.color = color;
		
		// line width of line drawn
		this.lineWidth = lineWidth;
		
		// coordinate of the points to plot
		this.points = [];

		// all possible unit allowed
		this.allUnits = { bytesps : 0, KBps : 1, MBps : 2 };
	}

Graph.prototype.draw = function()
	{
		var allPoints 		=	 this.points,
			lineColor 		=	 this.color,
			config 			=	 this.config,
			totalPoints		=	 allPoints.length,
			canvasHeight 	=	 canvas.height,
			initialX		=	 0,
			i;
		
		var currentStrokeColor =	 ctx.strokeStyle,
			currentLineWidth   =	 ctx.lineWidth;
		
		if( totalPoints < 15 )
		{
			initialX = 15 - totalPoints;
			initialX *= config.gridWidth;
		}
		// removing previous drawn graph
		ctx.clearRect( 0, 0, canvas.width, canvasHeight );
		
		ctx.strokeStyle 	=	 lineColor;
		ctx.lineWidth 		=	 this.lineWidth;
		
		ctx.beginPath();
		ctx.moveTo( initialX, canvasHeight - allPoints[0] );
		for( i = 1; i < totalPoints; i++ )
		{
			initialX += config.gridWidth;
			ctx.lineTo( initialX, canvasHeight - allPoints[i] );
		}
		ctx.stroke();
		ctx.closePath();
		
		// restoring previous values
		ctx.strokeStyle 	=	 currentStrokeColor;
		ctx.lineWidth 		=	 currentLineWidth;

	}
Graph.prototype.setPoints = function( speed, unit )
	{	
		var newCoor 	=	 this.speedToPixel * speed,
			config 		=	 this.config,
			max 		=	 0,
			totalPoints =    totalPoints = this.points.length,
			unitConversion,
			i;

		unitConversion = Math.pow( 1024, this.allUnits[unit] - this.allUnits[this.config.unit]);
		newCoor *= unitConversion;
		
		console.log( 'speed = '+speed + '    unit = '+unit);
		console.log( unitConversion);
		// inserting at the end of array
		this.points.push( newCoor );

		// removing the first element of array
		if( totalPoints + 1 > 15 )
		{
			this.points.shift();
		}

		for( i = 0; i < totalPoints; i++ )
		{
			if( max < this.points[i] )
			{
				max = this.points[i];
			}
		}
	
		if( max >= config.gridHeight * 5 /* five dividion in y axis */ )
		{
			// speed out of graph, need to rescale
			var newScale = parseInt( this.pixelToSpeed * max / 5 ) + 1;
			this.changeMarks( newScale );
			this.setScaleY( newScale );
		}
		else if( max <= config.gridHeight * 2 )
		{
			// speed getting low , need to rescale
			var newScale = parseInt( this.config.scaleY * 2 / 5 ) + 1;
			this.changeMarks( newScale );
			this.setScaleY( newScale );
		} 

	}
Graph.prototype.changeMarks = function( scale )
	{
		var allMarks 	=	 document.querySelectorAll('.graphBackground .axis li'),
			unitSpan 	= 	 document.querySelector('.graphBackground .axis .unit'),
			marker 		=	 '-',
			max			=	 scale * 5,
			unit 		=	 "KBps",
			multiplier  =    1,
			mark,
			i;
		if( max >= 3000 )
		{
			unit = "MBps";
			multiplier = 1024;
		}
		unitSpan.textContent = unit;

		for( i = 0; i < 5; i++ )
		{
			// in decreasing order like 25, 20, 15,....
			if( multiplier == 1 )
			{
				mark = scale * ( 5 - i );
			}
			else
			{
				mark = ( scale * ( 5 - i ) / multiplier ).toFixed(2);
			}
			allMarks[i].textContent = mark + marker;
		}
	}
Graph.prototype.setScaleY = function( newScale )
	{
		var newSpeedToPixel 	=	 this.config.gridHeight / newScale,
			totalPoints 		=	 this.points.length,
			i;
		
		for( i = 0; i < totalPoints; i++ )
		{
			this.points[i] = this.pixelToSpeed * this.points[i] * newSpeedToPixel;
		}

		this.config.scaleY = newScale;
		this.pixelToSpeed = 1 / newSpeedToPixel;
		this.speedToPixel = newSpeedToPixel; 
	}

var g1 = new Graph( 80, 42, 1, 5, '#5074a0', 2, 'KBps' );
//loop();
function loop()
{
	setTimeout( loop, 1000 /* 1 sec */ );
	eachSecond();
}

function eachSecond()
{
	var newSpeed = Math.random() * 4024 + 1;
	var unit = "KBps";// = units[ parseInt( Math.random() * units.length )];
	if( newSpeed >= 1024 )
	{
		newSpeed /= 1024;
		unit = "MBps";
	}
	console.log(newSpeed+' '+unit);
	g1.setPoints( newSpeed, unit );
	g1.draw();
}
