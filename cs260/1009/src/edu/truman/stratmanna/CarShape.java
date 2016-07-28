package edu.truman.stratmanna;

import java.awt.Graphics2D;
import java.awt.geom.Ellipse2D;
import java.awt.geom.Line2D;
import java.awt.geom.Point2D;
import java.awt.geom.Rectangle2D;


/**
 * A car that can be moved around.
 * @author Andrew Stratmann
 * ads5743@truman.edu
 * @version 8 October 2015
 */
public class CarShape implements MoveableShape
{
   /**
    * Constructs a car item.
    * @param x the left of the bounding rectangle
    * @param y the top of the bounding rectangle
    * @param width the width of the bounding rectangle
    */
   public CarShape(int x, int y, int width)
   {
      this.x = x;
      this.y = y;
      this.width = width;
      ORIGINAL_WIDTH = width;
   }
   
   /**
    * Translate the Car to a new location.
    * @param dx The amount of change in the x direction.
    * @param dy The amount of change in the y direction.
    */
   public void translate(int dx, int dy)
   {
      x += dx;
      y += dy;
   }
   
   /**
    * Draw a 2d graphics object.
    * @param g2 The object to be drawn.
    */
   public void draw(Graphics2D g2)
   {
      Rectangle2D.Double body
         = new Rectangle2D.Double(x, y + width / 6,
               width - 1, width / 6);
      Ellipse2D.Double frontTire
         = new Ellipse2D.Double(x + width / 6, y + width / 3,
               width / 6, width / 6);
      Ellipse2D.Double rearTire
         = new Ellipse2D.Double(x + width * 2 / 3, y + width / 3,
               width / 6, width / 6);
      
      // The bottom of the front windshield
      Point2D.Double r1
         = new Point2D.Double(x + width / 6, y + width / 6);
      // The front of the roof
      Point2D.Double r2
         = new Point2D.Double(x + width / 3, y);
      // The rear of the roof
      Point2D.Double r3
         = new Point2D.Double(x + width * 2 / 3, y);
      // The bottom of the rear windshield
      Point2D.Double r4
         = new Point2D.Double(x + width * 5 / 6, y + width / 6);
      Line2D.Double frontWindshield
         = new Line2D.Double(r1, r2);
      Line2D.Double roofTop
         = new Line2D.Double(r2, r3);
      Line2D.Double rearWindshield
         = new Line2D.Double(r3, r4);
      
      
      g2.draw(body);
      g2.draw(frontTire);
      g2.draw(rearTire);
      g2.draw(frontWindshield);
      g2.draw(roofTop);
      g2.draw(rearWindshield);
      
   }
   
   /**
    * Change the size of the car by way of a scaling factor.
    * @param factor The scaling factor
    */
   public void setSize(int factor)
   {
      width = (ORIGINAL_WIDTH * factor) / SCALING_FACTOR_NO_CHANGE;
   }
   
   private int x;
   private int y;
   private int width;
   private final int ORIGINAL_WIDTH;
   private static final int SCALING_FACTOR_NO_CHANGE = 50;


}