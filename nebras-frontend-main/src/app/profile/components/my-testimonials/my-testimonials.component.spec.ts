import { ComponentFixture, TestBed } from '@angular/core/testing';

import { MyTestimonialsComponent } from './my-testimonials.component';

describe('MyTestimonialsComponent', () => {
  let component: MyTestimonialsComponent;
  let fixture: ComponentFixture<MyTestimonialsComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [MyTestimonialsComponent]
    })
    .compileComponents();

    fixture = TestBed.createComponent(MyTestimonialsComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
